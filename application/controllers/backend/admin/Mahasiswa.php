<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Mahasiswa extends MY_Controller
{
    private $_path = 'backend/admin/mahasiswa/'; // Contoh 'backend/admin/dashboard'
    private $_table = 'mahasiswa';

    public function __construct()
    {
        parent::__construct();
        check_group("admin");
        $this->load->model($this->_path . 'M_Mahasiswa');
        $this->load->library(['upload', 'image_lib']);
    }

    public function index()
    {
        $this->templates->load([
            'title' => 'Mahasiswa',
            'type' => 'backend', // auth, frontend, backend
            'uri_segment' => $this->_path,
            'page' => $this->_path . 'index',
            'script' => $this->_path . 'index_js',
            'modals' => [
                $this->_path . 'modal/modal_tambah',
                $this->_path . 'modal/modal_ubah',
            ]
        ]);
    }

    public function data()
    {
        $this->M_Mahasiswa->generate_table();
    }

    public function get_fakultas()
    {
        return $this->output->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'data' => $this->db->like('nama', $this->input->get('search'))
                    ->get('fakultas')->result()
            ]));
    }

    public function get_prodi()
    {
        return $this->output->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'data' => $this->db->where('fakultas_id', $this->input->get('fakultas_id'))
                    ->like('nama', $this->input->get('search'))
                    ->get('prodi')->result()
            ]));
    }

    public function insert()
    {
        $config['upload_path'] = './uploads/mahasiswa/';
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['max_size'] = 2048;
        $config['encrypt_name'] = true;
        $config['remove_spaces'] = true;
        $this->upload->initialize($config);

        if (!$this->upload->do_upload("foto")) {
            return $this->output->set_content_type('application/json')
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'message' => $this->upload->display_errors()
                ]));
        }

        $config['image_library'] = 'gd2';
        $config['source_image'] = $this->upload->data('full_path');
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = TRUE;
        $config['width'] = 200;
        $config['height'] = 150;

        $this->image_lib->initialize($config);
        if (!$this->image_lib->resize()) {
            return $this->output->set_content_type('application/json')
                ->set_status_header(404)
                ->set_output(json_encode([
                    'status' => false,
                    'message' => $this->image_lib->display_errors()
                ]));
        }

        $this->M_Mahasiswa->insert(
            $this->_table,
            [
                'nim' => $this->input->post('nim', true),
                'nama' => $this->input->post('nama', true),
                'prodi_id' => $this->input->post('prodi_id', true),
                'fakultas_id' => $this->input->post('fakultas_id', true),
                'angkatan' => $this->input->post('angkatan', true),
                'foto' => $this->upload->data('file_name'),
                'foto_thumb' => $this->upload->data('raw_name') . '_thumb' . $this->upload->data('file_ext'),
                'is_active' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->ion_auth_model->user()->row()->id,
            ]
        );

        return $this->output->set_content_type('application/json')
            ->set_status_header(201)
            ->set_output(json_encode([
                'status' => true,
                'message' => 'Created successfuly'
            ]));
    }

    public function get_where()
    {
        return $this->output->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'message' => 'Found',
                'data' => $this->M_Mahasiswa->get_where("$this->_table a", [
                    'a.id' => $this->input->post('id', true),
                    'a.is_active' => '1'
                ])
            ]));
    }

    public function update()
    {
        $config['upload_path'] = './uploads/mahasiswa/';
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['max_size'] = 2048;
        $config['encrypt_name'] = true;
        $config['remove_spaces'] = true;
        $this->upload->initialize($config);
        if ($_FILES['foto']['error'] !== 4) {
            if (file_exists("./uploads/mahasiswa/{$this->input->post('old_foto')}")) {
                chmod("./uploads/mahasiswa/{$this->input->post('old_foto')}", 0777);
                chmod("./uploads/mahasiswa/{$this->input->post('old_foto_thumb')}", 0777);
                unlink("./uploads/mahasiswa/{$this->input->post('old_foto')}");
                unlink("./uploads/mahasiswa/{$this->input->post('old_foto_thumb')}");
            }

            if (!$this->upload->do_upload("foto")) {
                return $this->output->set_content_type('application/json')
                    ->set_status_header(404)
                    ->set_output(json_encode([
                        'status' => false,
                        'message' => $this->upload->display_errors()
                    ]));
            }

            $config['image_library'] = 'gd2';
            $config['source_image'] = $this->upload->data('full_path');
            $config['create_thumb'] = true;
            $config['maintain_ratio'] = true;
            $config['width'] = 200;
            $config['height'] = 150;

            $this->image_lib->initialize($config);
            if (!$this->image_lib->resize()) {
                return $this->output->set_content_type('application/json')
                    ->set_status_header(404)
                    ->set_output(json_encode([
                        'status' => false,
                        'message' => $this->image_lib->display_errors()
                    ]));
            }
        }

        $this->M_Mahasiswa->update($this->_table, [
            'nim' => $this->input->post('nim', true),
            'nama' => $this->input->post('nama', true),
            'prodi_id' => $this->input->post('prodi_id', true),
            'fakultas_id' => $this->input->post('fakultas_id', true),
            'angkatan' => $this->input->post('angkatan', true),
            'foto' => $_FILES['foto']['error'] === 4
                ? $this->input->post('old_foto') : $this->upload->data('file_name'),
            'foto_thumb' => $_FILES['foto']['error'] === 4
                ? $this->input->post('old_foto_thumb') : $this->upload->data('raw_name') . '_thumb' . $this->upload->data('file_ext'),
            'is_active' => '1',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->ion_auth_model->user()->row()->id,
        ], $this->input->post('id', true));

        return $this->output->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'message' => 'Updated successfuly'
            ]));
    }

    public function delete()
    {
        if (file_exists("./uploads/mahasiswa/{$this->input->post('old_foto')}")) {
            chmod("./uploads/mahasiswa/{$this->input->post('old_foto')}", 0777);
            chmod("./uploads/mahasiswa/{$this->input->post('old_foto_thumb')}", 0777);
            unlink("./uploads/mahasiswa/{$this->input->post('old_foto')}");
            unlink("./uploads/mahasiswa/{$this->input->post('old_foto_thumb')}");
        }

        $this->M_Mahasiswa->update($this->_table, [
            'is_active' => '0',
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by' => $this->ion_auth_model->user()->id,
        ], $this->input->post('id', true));

        return $this->output->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => true,
                'message' => 'Deleted successfuly'
            ]));
    }
}

/* End of file Home.php */
