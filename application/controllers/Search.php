<?php
class Search extends CI_Controller
{
    public function __construct(){
        parent::__construct();
        $this->load->model('M_Search','search');
    }
    public function index(){
        $this->load->view('template/public/pub_header');
        $this->load->view('public/search_page');
        $this->load->view('template/public/pub_footer');
    }

    // method untuk preprocessing
    public function prep($teks_dokumen){
        $this->load->library('preprocessing');
        return $this->preprocessing->preprocess($teks_dokumen);
    }

    // method untuk perhitungan vsm
    public function vsm($search_query, $dokumen){
        $this->load->library('vsm');
        return $this->vsm->get_rank($search_query, $dokumen);
    }

    public function processSearch($search_query){
        // step 1 mendapatkan kata dasar dari query (preprocessing query)
        $search_query = $this->prep($search_query);

        // step 2 mendapatkan dokumen ke array
        $koleksi_dokumen = $this->search->doctoArray();
        $arrayDokumen = [];
        foreach ($koleksi_dokumen->result_array() as $row) {
            $arrayDoc = [
                'id_doc' => $row['id'],
                // 'dokumen' => implode(" ", $this->prep($row['judul']))
                // langsung ambil dari token aja, karena preprocessing korpus sudah dilakukan ketika input/create dokumen
                'dokumen' => $row['token'],
            ];
            array_push($arrayDokumen, $arrayDoc);
        }

        // step 3 mendapatkan ranking dengan VSM
        $allRank = $this->vsm($search_query, $arrayDokumen);

        // step 4 memasukkan cos similarity ke database
        $jumlahDokumen = count($allRank['cosinus_similarity']);
        for ($i=0; $i < $jumlahDokumen; $i++) { 
            $id = $allRank['cosinus_similarity'][$i]['id_doc'];
            $bobot_cosim = $allRank['cosinus_similarity'][$i]['ranking'];
            $bobot_jaccard = $allRank['jaccard_similarity'][$i]['ranking'];
            $bobot_dice = $allRank['dice_similarity'][$i]['ranking'];
            // update
            $data = array(
                'cosim' => $bobot_cosim,
                'jaccard' => $bobot_jaccard,
                'dice' => $bobot_dice,
            );
            $this->search->updateBobot($data,$id);
        }
    }
    public function cari(){
        $search_query = $this->input->get('query');
        $tic = microtime(true);
        $this->processSearch($search_query);
        $toc = microtime(true);
        $data['waktu_pencarian'] = $toc-$tic;
        $data['koleksi_skripsi'] = $this->search->tampilHasil()->result();
        $data['keyword'] = $search_query;
        $this->load->view('template/public/pub_header');
        $this->load->view('public/result_page',$data);
        $this->load->view('template/public/pub_footer');
        $this->load->view('public/scripts/result_page');
    }
    
}

?>