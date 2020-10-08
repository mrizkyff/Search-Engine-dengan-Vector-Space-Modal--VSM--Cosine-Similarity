<?php
    class Analysis extends CI_Controller
    {
        public function __construct(){
            parent::__construct();
            $this->load->model('M_debug','mdebug');
        }
        public function index(){
            $this->load->view('template/admin/header');
            $this->load->view('template/admin/sidebar');
            $this->load->view('admin/analytics');
            $this->load->view('template/admin/footer');
            $this->load->view('admin/scripts/analytics');
            

        }
        // method preprocessing
        public function prep($teks_dokumen){
            $this->load->library('preprocessing');
            return $this->preprocessing->preprocess($teks_dokumen);
        }
        public function vsm($search_query, $dokumen){
            $this->load->library('vsm');
            return $this->vsm->get_rank($search_query, $dokumen);
        }
        // method untuk convert judul ke bentuk token
        public function generate_token(){
            $korpus = $this->mdebug->get_all_corpus();
            // $id = $korpus['id'];
            // $judul = $korpus['judul'];
            foreach ($korpus as $key => $value) {
                $data = array(
                    'token' => implode(' ',$this->prep($value['judul'])),
                );
                $this->mdebug->update_token($value['id'],$data);
            }
            echo 'sukses';
            // print_r($korpus);
        }
        public function proses_pencarian(){
            // // step 1 mengumpulkan korpus dan kueri
            // $kueri = 'Katalog Digital Pariwisata Semarang Berbasis Augmented Reality Untuk menjadikan Semarang Sebagai Smart City';
            // // $kueri = 'Daun berwarna putih';
            // $korpus = array(
            //     'g1' => 'Katalog Digital Pariwisata Semarang Berbasis Augmented Reality Untuk menjadikan Semarang Sebagai Smart City',
            //     'g2' => 'Penerapan Teknologi Augmented Reality Sebagai Media Promosi Universitas Dian Nuswantoro Berbasis Android',
            //     'g3' => 'RANCANG BANGUN APLIKASI KATALOG MAKANAN KOTA SEMARANG SEBAGAI SARANA REFERENSI BAGI WISATAWAN',
            // );
            $data = $this->input->post();
            $dataArray = [];
            foreach ($data as $key => $value) {
                $dataArray[$key] = implode(',<br>',$this->prep($value));
            }
            echo json_encode($dataArray);
            
            // step 2 preprocessing kueri 
            $kueri = $this->prep($kueri);

            // buat korpus ke dalam array
            $arrayDokumen = [];
            // var_dump($korpus);
            foreach ($korpus as $key => $value) {
                // print_r($this->prep($dokumen));
                $arrayDoc = [
                    'id_doc' => $key,
                    'dokumen' => implode(' ',$this->prep($value)),
                ];
                array_push($arrayDokumen, $arrayDoc);
            }
            print_r($arrayDokumen);

            $rank = $this->vsm($kueri, $arrayDokumen);
            print_r($rank);
        }
    }
    
?>