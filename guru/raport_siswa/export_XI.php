
<?php

include '../../init.php';
require_once('../../assets/plugin/TCPDF-master/tcpdf_include.php');
$db = new raport;
if($db->cekLoginNo_halamanGuru() === true) die;
// for admin akses halaman guru
if(!$db->cek_has_tahun_ajaran_semester_session_kelas_jurusan()) die;

$dbWK = new wali_kelas;
$dbTA = new tahun_ajaran;
$dbS = new siswa;
$dbIS = new identitas_sekolah;
$dbK = new kelas;
$siswa_detail_id = filter_input(INPUT_GET, 'siswa_detail_id', FILTER_SANITIZE_STRING);
$arrTahun_ajaran = explode(".", filter_input(INPUT_GET, 'tahun_ajaran_id', FILTER_SANITIZE_STRING));
$arrSemester = explode(".", filter_input(INPUT_GET, 'semester_id', FILTER_SANITIZE_STRING));
$tahun_ajaran_id = $arrTahun_ajaran[0]??null;
$tahun_ajaran = $arrTahun_ajaran[1]??null;
$semester_id = $arrSemester[0]??null;
$semester = $arrSemester[1]??null;

// set nama wali kelas dan kelas jurusan
$nama_wali_kelas = filter_input(INPUT_GET, 'nama_wali_kelas', FILTER_SANITIZE_STRING);
$kelasJurusan = filter_input(INPUT_GET, 'kelasJurusan', FILTER_SANITIZE_STRING);
$data_siswa = $dbS->get_one_siswa_detail($siswa_detail_id, 'masih_sekolah', $select="sd.nama_siswa, sd.nisn, sd.nama_ayah");
$arrKelasKetSiswa = explode(".", $_SESSION['RAPORT']['kelas']);

if($nama_wali_kelas != null && $kelasJurusan != null) {
    $data_wali_kelas = $nama_wali_kelas;
    $kelasJurusan = $kelasJurusan;
} else {
    $data_wali_kelas = $dbWK->get_one_wali_kelas(($_SESSION['RAPORT']['wali_kelas_id']??''), "nama")['nama']??'';
    $kelasJurusan = $arrKelasKetSiswa[0].' '.$_SESSION['RAPORT']['jurusan'].' '.($arrKelasKetSiswa[1]??'');
}

$pdf = new TCPDF('P', 'cm', 'A4', true, 'UTF-8', false);
// set margins
$pdf->SetMargins(1.27,1.27,1.27,true);
// set auto breaks
$pdf->SetAutoPageBreak(true, 1.27);
// menghapus header dan footer default
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->setTitle("Export/Print RAPORT");
// halaman 1
$pdf->AddPage();
$page1 = '
<style>
table{
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    font-size: 10px;
}
</style>
	<table style="font-size: 10px">
	    <tr>
	        <td colspan="2" align="center"  style="font-size: 25px" >RAPOR PESERTA DIDIK</td>
        </tr>
	    <tr>
	        <td width="100">Nama Sekolah</td>
			<td width="10">:</td>
			<td width="200">SMK BINA PROFESI</td>
        </tr>
        <tr>
	        <td width="100">Alamat</td>
			<td width="10">:</td>
			<td width="200">Jl. Ceremai Ujung No.234 Kota Bogor</td>
        </tr>
		<tr>
			<td width="100">Nama Peserta Didik</td>
			<td width="10">:</td>
			<td width="200">'.($data_siswa['nama_siswa']??'').'</td>

			<td width="70">Kelas</td>
			<td width="10">:</td>
			<td>'.$kelasJurusan.'</td>
		</tr>
		<tr>
			<td>NIS</td>
			<td>:</td>
			<td>'.($data_siswa['nisn']??'').'</td>

			<td>Semester/TP</td>
			<td>:</td>
			<td>'.$semester.'/'.$tahun_ajaran.'</td>
		</tr>
	</table>
	<br>

	<h4>A. Sikap</h4>
	<table border="1" cellspacing="0" cellpadding="10">
		<tr>
			<td align="justify">'.($db->tampil_sikap($siswa_detail_id, $tahun_ajaran_id, $semester_id)['sikap']??''). '</td>
			
		</tr>
	</table>

	<h4>B. Capaian Pengetahuan dan Keterampilan</h4>
	<table border="1" cellspacing="0" cellpadding="4">
		<tr style="background-color: #c6c6c6">
			<th rowspan="2" width="25"><br><br>No</th>
			<th rowspan="2" width="221.5"><br><br>Mata pelajaran</th>
			<th rowspan="2" width="35"><br><br>Kkm</th>
			<th colspan="2" width="118" align="center">Pengetahuan</th>
			<th colspan="2" width="118" align="center">Keterampilan</th>
		</tr>
		<tr style="background-color: #c6c6c6">
			<th align="center" width="40">Angka</th>
			<th align="center" width="78">Predikat</th>

			<th align="center" width="40">Angka</th>
			<th align="center" width="78">Predikat</th>
		</tr>';

$cek_has_nilai_deskripsi = $db->cek_has_nilai_deskripsi($siswa_detail_id, $tahun_ajaran_id, $semester_id);
if($cek_has_nilai_deskripsi > 0) {
    $nilai_deskripsi = $db->tampil_nilai($siswa_detail_id, $tahun_ajaran_id, $semester_id);
    $tesss = print_r($nilai_deskripsi, true);
//    $page1 .= '' . $tesss . '';
    $kelompok_sebelum = '';
    $no = 1;
    $urut_mapel = array('Pendidikan Agama','Pendidikan Pancasila ', 'Bahasa Ind', 'Mate', 'Bahasa Ing', 'Pendidikan Jasmani', 'Penataan Pro', 'Bisnis On', 'Pengelolaan Bisnis', 'Administrasi Tra', 'Produk Kreatif dan');
//    $urut_mapel_tkj = array('Pendidikan Agama','Pendidikan Pancasila ', 'Bahasa Ind', 'Mate', 'Bahasa Ing', 'Pendidikan Jasmani', 'Penataan Pro', 'Bisnis On', 'Pengelolaan Bisnis', 'Administrasi Tra', 'Produk Kreatif dan');
//    $urut_mapel_bdp = array('Pendidikan Agama','Pendidikan Pancasila ', 'Bahasa Ind', 'Mate', 'Bahasa Ing', 'Pendidikan Jasmani', 'Penataan Pro', 'Bisnis On', 'Pengelolaan Bisnis', 'Administrasi Tra', 'Produk Kreatif dan');
//    $ketemu = false;
//    $nilai_anyar = array();
//    if(strpos($kelasJurusan, 'TKJ') !== false){
//        $urut_mapel = $urut_mapel_tkj;
//    }elseif (strpos($kelasJurusan, 'BDP') !== false){
//        $urut_mapel = $urut_mapel_bdp;
//    }elseif (strpos($kelasJurusan, 'OTKP') !== false){
//        $urut_mapel = $urut_mapel_otkp;
//    }
    $no_arr = 0;
    for($i = 0; $i < count($urut_mapel); $i++){
        $no_arr = 0;
        $ketemu = false;
        while($ketemu == false){
            if(strpos($nilai_deskripsi[$no_arr]['nama_mapel'], $urut_mapel[$i]) !== false){
                $ketemu = true;
                array_push($nilai_anyar, $nilai_deskripsi[$no_arr]);
            }
            $no_arr++;
        }
    }
    $nilai_deskripsi = $nilai_anyar;
    foreach($nilai_deskripsi as $n) {

        if($kelompok_sebelum != $n['kelompok_mapel']) {
            $kelompok_sebelum = $n['kelompok_mapel'];
            $page1 .= '<tr>
					<th colspan="7">Kelompok '.$n['kelompok_mapel'].'</th>
				</tr>
			';
        }
        $page1 .= '<tr>
				<td align="center">'.$no.'</td>
				<td>'.$n['nama_mapel'].'</td>
				<td align="center">'.$n['kkm'].'</td>
				<td align="center">'.str_replace(".", ",", $n['nilai_p']).'</td>
				<td align="center">'.$db->generate_predikat($n['nilai_p'], $n['predikat_d'], $n['predikat_c'], $n['predikat_b'], $n['predikat_a']).'</td>
				<td align="center">'.str_replace(".", ",", $n['nilai_k']).'</td>
				<td align="center">'.$db->generate_predikat($n['nilai_k'], $n['predikat_d'], $n['predikat_c'], $n['predikat_b'], $n['predikat_a']).'</td>
			</tr>';
        $no++;
    }
} else {
    $page1 .= '<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
}
$page1 .= '</table>';
$pdf->writeHTML($page1, true, true, true, true, '');

// halaman 2
$page2 = '
	<h4>C. Deskripsi pencapaian kompetensi</h4>
	<table border="1" cellspacing="" cellpadding="4" >
		<tr style="background-color: #c6c6c6">
			<th rowspan="2" width="25" style="font-size: 10px"><br><br>No</th>
			<th rowspan="2" width="100" style="font-size: 10px"><br><br>Mata pelajaran</th>
			
			<th colspan="3" width="200" align="center" style="font-size: 10px">Pengetahuan</th>
			<th colspan="3" width="200" align="center" style="font-size: 10px">Keterampilan</th>
		</tr>
		<tr style="background-color: #c6c6c6">
		    <th width="40" style="font-size: 10px">Angka</th>
			<th align="center" style="font-size: 10px"  width="35">Predikat</th>
			<th align="center" style="font-size: 10px"  width="125">Deskripsi</th>
            <th width="40" style="font-size: 10px">Angka</th>
			<th align="center" style="font-size: 10px" width="35">Predikat</th>
			<th align="center" style="font-size: 10px" width="125">Deskripsi</th>
		</tr>';
if($cek_has_nilai_deskripsi > 0) {
    $no = 1;
    $kelompok_sebelum = '';
    foreach($nilai_deskripsi as $d) {
        if($kelompok_sebelum != $d['kelompok_mapel']) {
            $kelompok_sebelum = $d['kelompok_mapel'];
            $page2 .= '<tr>
					<th colspan="8" style="font-size: 10px">Kelompok '.$d['kelompok_mapel'].'</th>
				</tr>';
        }

        $kd_x = [
            "Matematika" => "Memahami Pembelajaran",
            "Bahasa Inggris & Bahasa Asing lainnya" => "Memberikan informasi",
            "Bahasa Inggris & Bahasa Asing lainnya " => "Memberikan informasi",
            "Bahasa Indonesia" => "mengevaluasi pengajuan, penawaran, dan persetujuan dalam teks negosiasi lisan maupun tertulis, Menganalisis aspek makna dan kebahasaan dalam teks biografi, Menganalisis unsur pembangun puisi",
            "Pendidikan Agama dan Budi Pekerti" => "Dalam memberikan informasi",
            "Pendidikan Agama & Budi Pekerti" => "Dalam memberikan informasi",
            "Pendidikan Pancasila dan Kewarganegaraan" => "Menghayati hakikat bangsa dan negara sebagai anugerah Tuhan yang maha Esa",
            "Sejarah Indonesia" => "Perkembangan politik dan ekonomi Indonesia pada masa awal keerdekaan hingga Demokrasi Terpimpin",
            "Pendidikan Pancasila & Kewarganegaraan" => "Menghayati hakikat bangsa dan negara sebagai anugerah Tuhan yang maha Esa",
            "Seni Budaya" => "Memahami konsep budaya dan Mempresentasikan konsep budaya",
            "Seni Budaya " => "Memahami konsep budaya dan Mempresentasikan konsep budaya",
            "Pendidikan Jasmani Olahraga & Kesehatan" => "Memahami Pembelajaran",
            "Kimia" => "Memahami Pembelajaran",
            "Simulasi dan Komunikasi Digital / KKPI" => "mengevaluasi paragraf deskriptif, argumentatif, naratif, dan persuasif. Menganalisis fitur yang tepat untuk pembuatan slide, dan pembuatan e-book.",
            "Fisika" => "Memahami Pembelajaran",
            "Komputer dan Jaringan Dasar" => "Menginstalasi jaringan lokal (LAN).",
            "Pemrograman Dasar" => "Memahami Dasar Dasar Bahasa Pemrograman C++",
            "Dasar Design Grafis" => "Mendesain efek pada gambar vector",
            "Sistem Komputer" => "Memahami Pembelajaran",
            "Bahasa Sunda" => "menganalisis bentuk dan type aksara sunda sesuai dengan kaidah kaidahnya",
            "Administrasi Umum" => "Memahami Pembelajaran",
            "Ekonomi Bisnis" => "Memahami Pembelajaran",
            "Komunikasi Bisnis" => "prosedur penulisan surat niaga dalam bidang bisnis dan mampu membuat surat niaga dalam bisnis ",
            "Perencanaan Bisnis" => "menerapkan prosedur pembuatan proposal usaha ",
            "Marketing" => "Memahami Pembelajaran",
            "IPA" => "Limbah rumah tangga dan pabrik",
            "Korespondensi" => "mengetahui  macam- macam surat  Pribadi, Surat Dinas, Surat Niaga",
            "Teknologi Perkantoran" => "Memahami Pembelajaran",
            "Kearsipan" => "Arsip dan Kearsipan, menerapkan penaganan surat masuk dan surat keluar, menerapkan klasifikasi dan indeks arsip"
        ];
        $kd_xi = [
            "Matematika" => "Memahami Pembelajaran",
            "Bahasa Inggris & Bahasa Asing lainnya" => "sosial, struktur teks dan unsur kebahasaan interaksi transaksional lisan dan tulis yang melibatkan tindakan memberi dan meminta informasi terkait  saran dan tawaran ,pendapat dan pikiran, beberapa teks khusus undangan resmi  sesuai dengan konteks penggunaannya",
            "Bahasa Indonesia" => "menganalisis struktur dan kaidah teks anekdot  mengenai permasalahan sosial, ingkungan, dan kebijakan publik serta  teks laporan hasil observasi, baik melalui lisan maupun tulisan  serta  sangat baik dalam memahami dan menganalisis teks eksposisi  baik secara lisan maupun tulisan",
            "Pendidikan Agama dan Budi Pekerti" => "Memahami Pembelajaran",
            "Pendidikan Agama & Budi Pekerti" => "Memahami Pembelajaran",
            "Sejarah Indonesia" => "Memahami Pembelajaran",
            "Pendidikan Pancasila dan Kewarganegaraan" => "Sistem hukum dan peradilan di Indonesia dan peradilan internasional serta peran Indonesia dalam Perdamaian Dunia",
            "Pendidikan Pancasila & Kewarganegaraan" => "Sistem hukum dan peradilan di Indonesia dan peradilan internasional serta peran Indonesia dalam Perdamaian Dunia",
            "Pendidikan Jasmani Olahraga & Kesehatan" => "Teknik dasar salah satu aktivitas olahraga permainan bola kecil untuk menghasilkan koordinasi gerak",
            "Pendidikan Jasmani Olahraga dan Kesehatan" => "Teknik dasar salah satu aktivitas olahraga permainan bola kecil untuk menghasilkan koordinasi gerak",
            "Administrasi Sistem Jaringan" => " DHCP Server, Mengkonfigurasi DHCP server",
            "Teknologi Layanan Jaringan" => "Standar komunikasi data",
            "Administrasi Infrastruktur Jaringan" => "analisa kebutuhan vLAN",
            "Teknologi Jaringan Berbasis Luas (WAN)" => "Memperbaiki jaringan nirkabel",
            "Produk Kreatif dan Kewirausahaan" => "Strategi promosi bisnis ritel",
            "Penataan Produk" => "Menerapkan layout/ Planogram penataan Produk dan Membuat layout/ Planogram produk",
            "Pengelolaan Bisnis Ritel" => "Advertising dan personal selling dalam bisnis ritel dan sales promotion dan public relation bisnis ritel",
            "Administrasi Transaksi" => "mengetahui  alat bantu verifikasi & komunikasi dalam transaksi, perawatan mesin-mesin transaksi dan laporan hasil penjualan",
            "Bisnis Online" => "Membuat iklan online, Membuat Blog",
            "Otomatisasi Tata Kelola Sarana Prasarana" => "Memahami Pembelajaran",
            "Otomatisasi Tata Kelola Humas Keprotokolan" => " khalayak humas, rprofesi humas,serta pelayanan prima. ",
            "Otomatisasi Tata Kelola Kepegawaian" => "cara penilaian kerja dan prestasi pegawai negeri, cara sistem penggajian dan tunjangan pegawai negeri",
            "Otomatisasi Tata Kelola Keuangan" => " pembuatan laporan keuangan pertanggungjawaban keuangan",
        ];
        if($arrKelasKetSiswa[0] == "X"){
            $kd = $kd_x;
        }elseif ($arrKelasKetSiswa[0] == "XI"){
            $kd = $kd_xi;
        }
        $predikat_p = $db->generate_predikat($d['nilai_p'], $d['predikat_d'], $d['predikat_c'], $d['predikat_b'], $d['predikat_a']);
        switch ($predikat_p){
            case 'A':
                $deskripsi_p = "Memiliki nilai pengetahuan yang sangat baik dalam ";
                break;
            case 'B':
                $deskripsi_p = "Memiliki nilai pengetahuan yang baik dalam ";
                break;
            case 'C':
                $deskripsi_p = "Memiliki nilai pengetahuan yang cukup dalam ";
                break;
            case 'D':
                $deskripsi_p = "Memiliki nilai pengetahuan yang kurang dalam ";
                break;
            default:
                $deskripsi_p = "";
        }

        $predikat_k = $db->generate_predikat($d['nilai_k'], $d['predikat_d'], $d['predikat_c'], $d['predikat_b'], $d['predikat_a']);
        switch ($predikat_k){
            case 'A':
                $deskripsi_k = "Memiliki nilai keterampilan yang sangat baik dalam ";
                break;
            case 'B':
                $deskripsi_k = "Memiliki nilai keterampilan yang baik dalam ";
                break;
            case 'C':
                $deskripsi_k = "Memiliki nilai keterampilan yang cukup dalam ";
                break;
            case 'D':
                $deskripsi_k = "Memiliki nilai keterampilan yang kurang dalam ";
                break;
            default:
                $deskripsi_k = "";
        }

        $page2 .= '<tr>
               
				<td align="center" style="font-size: 10px">'.$no.'</td>
				<td style="font-size: 10px">'.$d['nama_mapel'].'</td>
				<td align="center" style="font-size: 10px">'.$d['nilai_p'].'</td>
				<td align="center" style="font-size: 10px">'.$predikat_p.'</td>
				<td align="justify" style="font-size: 10px">'.$deskripsi_p. $kd[$d['nama_mapel']] .'</td>
				<td align="center" style="font-size: 10px">'.$d['nilai_k'].'</td>
				<td align="center" style="font-size: 10px">'.$db->generate_predikat($d['nilai_k'], $d['predikat_d'], $d['predikat_c'], $d['predikat_b'], $d['predikat_a']).'</td>
				<td align="justify" style="font-size: 10px">'.$deskripsi_k . $kd[$d['nama_mapel']] .'</td>
			</tr>';
        $no++;
    }
} else {
    $page2 .= '<tr><td></td><td></td><td></td><td></td></tr>';
}
$page2 .= '</table>';
$pdf->writeHTML($page2, true, true, true, true, '');

// halaman 3
$pdf->AddPage();
$praktik_kerja_industri = $db->tampil_praktik_kerja_industri($siswa_detail_id, $tahun_ajaran_id, $semester_id);
if($praktik_kerja_industri) {
    $offsetRowPrakerin = 3-count($praktik_kerja_industri??[]);
    $page31 = '<h4>D. Praktik Kerja Industri</h4>
		<table border="1" cellspacing="" cellpadding="8">
		<tr style="background-color: #c6c6c6">
			<th width="30">No</th>
			<th>Mitra DU/DI</th>
			<th width="150">Lokasi</th>
			<th>Lamanya(Bulan)</th>
			<th width="130.5">Keterangan</th>
		</tr>';

    $no = 1;
    foreach($praktik_kerja_industri as $prin) {
        $page31 .= '<tr>
			<td>'.$no.'</td>
			<td>'.$prin['mitra_du_di'].'</td>
			<td>'.$prin['lokasi'].'</td>
			<td>'.$prin['lamanya'].'</td>
			<td>'.$prin['keterangan'].'</td>
		</tr>';
        $no++;
    }
    for($orPrin = 1; $orPrin <= $offsetRowPrakerin; $orPrin++) {
        $page31 .= '<tr><td></td><td></td><td></td><td></td><td></td></tr>';
    }
    $page31 .= '</table>';
    $pdf->writeHTML($page31, true, true, true, true, '');
    $pdf->Ln(0.5);
    $page32 = '<h4>E. Ekstrakurikuler</h4>';
    $page33 = '<h4>F. Prestasi</h4>';
    $page41 = '<br><h4>G. Ketidakhadiran</h4>';
    $page42 = '<h4>H. Catatan Wali kelas</h4>';
    $page43 = '<h4>I. Tanggapan Orang Tua/Wali</h4>';
} else {
    $page32 = '<h4>D. Ekstrakurikuler</h4>';
    $page33 = '<h4>E. Prestasi</h4>';
    $page41 = '<h4>F. Ketidakhadiran</h4>';
    $page42 = '<h4>G. Catatan Wali kelas</h4>';
    $page43 = '<h4>H. Tanggapan Orang Tua/Wali</h4>';
}

// ekskul
//$page32 .= '
//	<table border="1" cellspacing="" cellpadding="6">
//		<tr style="background-color: #D4F2DB">
//			<th width="30">No</th>
//			<th width="200">Ekstrakurikuler</th>
//			<th width="45">Nilai</th>
//			<th width="242.5">Keterangan</th>
//		</tr>';
//$ekstrakurikuler = $db->tampil_ekstrakurikuler($siswa_detail_id, $tahun_ajaran_id, $semester_id);
//$offsetRowEskul = 3-count($ekstrakurikuler??[]);
//if($ekstrakurikuler) {
//    $no = 1;
//    foreach($ekstrakurikuler as $e) {
//        $page32 .= '<tr>
//				<td align="center">'.$no.'</td>
//				<td>'.$e['nama_ekstrakurikuler'].'</td>
//				<td align="center">'.str_replace(".", ",", $e['nilai']).'</td>
//				<td>'.$e['keterangan'].'</td>
//			</tr>';
//        $no++;
//    }
//}
//for($orE = 1; $orE <= $offsetRowEskul; $orE++) {
//    $page32 .= '<tr><td></td><td></td><td></td><td></td></tr>';
//}
//$page32 .= '</table>';
//$pdf->writeHTML($page32, true, true, true, true, '');
//$pdf->Ln(0.5);
//$page33 .= '
//	<table border="1" cellspacing="" cellpadding="6">
//		<tr style="background-color: #D4F2DB">
//			<th width="30">No</th>
//			<th width="200">Jenis prestasi</th>
//			<th width="287.5">Keterangan</th>
//		</tr>';
//$prestasi = $db->tampil_prestasi($siswa_detail_id, $tahun_ajaran_id, $semester_id);
//$offsetRowPrestasi = 3-count($prestasi??[]);
//if($prestasi) {
//    $no = 1;
//    foreach($prestasi as $p) {
//        $page33 .= '<tr>
//				<td align="center">'.$no.'</td>
//				<td>'.$p['jenis_prestasi'].'</td>
//				<td>'.$p['keterangan'].'</td>
//			</tr>';
//        $no++;
//    }
//}
//for($orP = 1; $orP <= $offsetRowPrestasi; $orP++) {
//    $page33 .= '<tr><td></td><td></td><td></td></tr>';
//}
//$page33 .= '</table>';
//$pdf->writeHTML($page33, true, true, true, true, '');

// halaman 4
$page41 .= '
	<table border="1" cellspacing="" cellpadding="4">
		<tr style="background-color: #c6c6c6">
			<th style="font-size: 10px">Sakit</th>
			<th style="font-size: 10px">Izin</th>
			<th style="font-size: 10px">Tanpa keterangan</th>
			<th style="font-size: 10px">Bolos</th>
		</tr>';
$ketidakhadiran = $db->tampil_ketidakhadiran($siswa_detail_id, $tahun_ajaran_id, $semester_id);
$page41 .= '<tr>
			<td style="font-size: 10px">'.($ketidakhadiran['sakit']??'').'</td>
			<td style="font-size: 10px">'.($ketidakhadiran['izin']??'').'</td>
			<td style="font-size: 10px">'.($ketidakhadiran['tanpa_keterangan']??'').'</td>
			<td style="font-size: 10px">'.($ketidakhadiran['bolos']??'').'</td>
		</tr>
	</table>';
$pdf->writeHTML($page41, true, true, true, true, '');
$pdf->Ln(0.5);
$page42 .= '
	<table border="1" cellspacing="" cellpadding="20">
		<tr>
			<td>'.($db->tampil_catatan_wali_kelas($siswa_detail_id, $tahun_ajaran_id, $semester_id)['catatan']??'').'</td>
		</tr>
	</table>';
$pdf->writeHTML($page42, true, true, true, true, '');
$pdf->Ln(0.5);
$page43 .= '
	<table border="1" cellspacing="" cellpadding="20">
		<tr>
			<td></td>
		</tr>
	</table>';
if($semester == 2) {
    $page43 .= '<p style="font-size: 10px">Keputusan :<br>Berdasarkan hasil yang dicapai pada semester 1 dan 2, peserta didik ditetapkan :</p>';

    $status_akhir = $db->get_one_status_akhir_semester($siswa_detail_id, $tahun_ajaran_id, $semester_id)['status_akhir']??'';

    if($status_akhir) {
        if($status_akhir == "lulus" || $status_akhir == "tidak_lulus") {
            $page43 .= '<p style="font-size: 10px"><b>'.strtoupper(str_replace("_", " ", $status_akhir)).'</b></p>';

        } else {
            $arrStatus_akhir = explode("_", $status_akhir);
            $arrKelas = explode(".", $dbK->get_one_kelas($arrStatus_akhir[3]??'', 'kelas')['kelas']??'');
            $jurusan = $dbK->get_jurusan_where_kelas_id($arrStatus_akhir[3]??'', "nama_jurusan")['nama_jurusan']??'';

            $page43 .= '<p style="font-size: 10px"><span style="text-transform: capitalize;">'.$arrStatus_akhir[0].'</span> '.($arrStatus_akhir[1]??'').' '.($arrStatus_akhir[2]??'').' <b>'.($arrKelas[0]??'').' '.$jurusan.' '.($arrKelas[1]??'').'</b></p>';
        }
    }
}
$pdf->writeHTML($page43, true, true, true, true, '');
$pdf->Ln(1);
//$pdf->Image('pa_bagio.png', 15, 140, 25, 113, 'PNG', '', '', false, 150, '', false, false, 0, false, false, false);
$identitas_sekolah = $dbIS->tampil_identitas_sekolah('nama_kepala_sekolah, nip_kepala_sekolah, kabupaten, nama_sekolah');
switch ($data_wali_kelas){
    case 'Ahmad Subagio, S.Ag':
        $ttd_walas = "pa_bagio.png";
        break;
    case 'Herlisna, SE':
        $ttd_walas = "bu_herlisna.png";
        break;
    default:
        $ttd_walas = "";
}
$tandaTangan = '
	<table border="0" cellspacing="" cellpadding="0" style="font-size: 10px">
		<tr>
			<td align="center" style="font-size: 10px">Orang Tua/Wali</td>
			<td></td>
			<td align="center" style="font-size: 10px">'.($identitas_sekolah['kabupaten']??'').', '.date('d').' '.$dbS->bulanIndo(date("m")).' '.date("Y").'<br>Wali Kelas</td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td align="center" ></td>
		</tr>
		<tr>
			<td align="center" style="font-size: 10px">'.($data_siswa['nama_ayah']??'').'</td>
			<td></td>
			<td align="center" style="font-size: 10px">'.$data_wali_kelas.'</td>
		</tr>

		<tr>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td align="center">Mengetahui Kepala sekolah<br>'.($identitas_sekolah['nama_sekolah']??'').'</td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td align="center"></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td align="center">'.($identitas_sekolah['nama_kepala_sekolah']??'').'<br>NIP.'.($identitas_sekolah['nip_kepala_sekolah']??'').'</td>
			<td></td>
		</tr>
	</table>
';
$pdf->writeHTML($tandaTangan, true, true, true, true, '');
ob_end_clean();
$pdf->Output('RAPORT '.$kelasJurusan.' '.($data_siswa['nama_siswa']??'').' semester '.$semester.'.pdf','I');