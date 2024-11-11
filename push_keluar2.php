<?php
require 'controller/callApi.php';
require 'controller/DbConfig.php';
require 'util/AkmUtil.php';
require 'helper/feeder_helper.php';

//set limit
set_time_limit(3600);
ini_set('memory_limit', '-1');

//Configurasi
$api = new callApi();
$db = new DbConfig();
$akm_util = new AkmUtil();
$feeder_helper = new FeederHelper();

//getToken
$token = $feeder_helper->get_token_feeder();

$arrNim = array("1101190541",

);

$id_smt = $_GET['id_smt']; //cth:20182(tahun ajaran 2018/2019 semester 2)
$cek_keluar = 0; //0="aktif";1="lulus";2="aktif&lulus";
$aktifcek_undir = 0; //0="aktif";1="undir";2="aktif&undir";
$cek_cuti = 0; //0="aktif";1="cuti";
$cek_mangkir = 0; //0="aktif";1="mangkir";

$sc = substr($id_smt, 2, 2) . "" . (substr($id_smt, 2, 2) + 1); //cth 1819(tahun ajaran 2018 / 2019)
$th = substr($id_smt, 0, 4);
$smt = substr($id_smt, -1, 1);

$row = $_GET['row'];
$pushType = $_GET['push_type'];
$limit = 1;
$nim_push=$arrNim[$row];

if($pushType == "normal"){
	$student = $db->getStudentAKM("(OUTDATE IS NULL OR ID_SMT_LULUS >= ".$id_smt.") AND ID_SMT_MASUK <= ".$id_smt." AND ID_REG_PD IS NOT NULL AND ID_REG_PD NOT LIKE '%Error%'",$row,($row+$limit));
}else if($pushType == "update"){
	//type update all data
	$student = $db->getStudentAKM("(OUTDATE IS NULL OR ID_SMT_LULUS >= ".$id_smt.") AND ID_SMT_MASUK <= ".$id_smt,$row,($row+$limit));
}else if($pushType == "error"){
	$student = $db->getStudentAKM("(OUTDATE IS NULL OR ID_SMT_LULUS >= ".$id_smt.") AND ID_SMT_MASUK <= ".$id_smt." AND ID_REG_PD IS LIKE '%Error%'",$row,($row+$limit));
}else if($pushType == "case"){
	$student = $db->getStudentAKM("STUDENTID  ='".$nim_push."'",1,2);
}else{
	header("HTTP/1.0 404 Not Found");
	echo "<h1>404 - Page Not Found</h1>";
	echo "<p>The page you are looking for does not exist.</p>";
	die();
}

echo "<pre>";
print_r ($student);
echo "</pre>";
//die();

if(!empty($student)){ 
	foreach ($student as $key => $value) {
	$data = (array_change_key_case($value, CASE_LOWER));	 
		//inisialisasi nim students	
		$nim=$data['studentid'];
		
		//Cek Lulus		
		$cek_lulus = $db->CekLulus("STUDENTID='".$nim."' AND SCHOOLYEAR <= ".$sc);
		 echo "<pre>Data Lulus";
	 print_r ($cek_lulus);
	 echo "</pre>";
		// die();
		
		if($data['id_smt_lulus']>=$id_smt or $data['id_smt_lulus']==""){
			// cek lulus			
			if (empty($cek_lulus)) {
				$cek_keluar = 0;
			} else {
				//$cek_lulus_smt = $db->CekLulus("STUDENTID='".$nim."' AND SCHOOLYEAR =".$sc." AND SEMESTER = ".$smt);
				//if (empty($cek_lulus_smt)) {
				//	$cek_keluar = 0;
					
				//	echo $sc."</br>";
				//	echo $smt."</br>";
				//} else {
					$cek_AKM = $db->getKrs("NIPD='".$nim."' AND SCHOOLYEAR = ".$sc." AND SEMESTER = ".$smt,1,2);	
					if (empty($cek_AKM)) {
						$cek_keluar = 1;
						// TODO PUSH LULUS
						echo "PUSH LULUS</br>";
						$akm_util->insertMahasiswaKeluar(1, $nim, $data['id_smt_lulus'], $token, $sc, $smt);
					} else {
						$cek_keluar = 2;					
						//TODO PUSH LULUS & PUSH AKTIF
						//echo "PUSH AKTIF</br>";
						//$akm_util->insertAktivitasKuliahMahasiswa($nim, "A", $token, $id_smt);
						echo "PUSH LULUS</br>";						
						$akm_util->insertMahasiswaKeluar(1, $nim, $data['id_smt_lulus'], $token, $sc, $smt);
					}

				//}
			}


			//Cek DO/Undur diri
			if ($cek_keluar == 0) {
				//Cek_undur_diri
				$cek_DOUndir = $db->CekDo("STUDENTID='".$nim."' AND SCHOOLYEAR <=".$sc);
				if (empty($cek_DOUndir)) {
					$cek_undir = 0;
				} else {
					$cek_DOUndir_smt = $db->CekDo("STUDENTID='".$nim."' AND SCHOOLYEAR ='".$sc."' AND SEMESTER = ".$smt);
					if (empty($cek_DOUndir_smt)) {
						$cek_undir = 0;
					} else {
						$cek_AKM = $db->getKrs("NIPD='".$nim."' AND SCHOOLYEAR = ".$sc." AND SEMESTER = ".$smt,1,2);
						if (empty($cek_AKM)) {
							$cek_undir = 1;
							//TODO PUSH UNDIR
							// TODO CEK NON-AKTIF
							// CEK SKIP REGISTRATION
							if(!empty($skipRegistration)){
								//push non aktif
								echo "PUSH DO/UNDIR</br>";
								$akm_util->insertMahasiswaKeluar(4, $nim, $data['id_smt_lulus'], $token, $sc, $smt);
							}
						} else {
							$cek_undir = 2;
							//TODO PUSH UNDIR & PUSH AKTIF
							echo "PUSH DO/UNDIR & AKTIF</br>";	
							//$akm_util->insertAktivitasKuliahMahasiswa($nim, "A", $token, $id_smt);
							$akm_util->insertMahasiswaKeluar(4, $nim, $data['id_smt_lulus'], $token, $sc, $smt);	
						}

					}
				}
			}

			//Cek_cuti
			/*if ($cek_keluar == 0 && $cek_undir == 0) {
				$cek_CutiMhs_smt = $db->getLeave("STUDENTID='".$nim."' AND STARTLEAVESCHOOLYEAR = ".$sc." AND STARTLEAVESEMESTER = ".$smt." AND STATUS_DIRAKAD='Y'",1,2);
				if (empty($cek_CutiMhs_smt)) {
					$cek_cuti = 0;
				} else {
					$cek_cuti = 1;		
					// TODO PUSH CUTI
					echo "PUSH CUTI</br>";
					$akm_util->insertAktivitasKuliahMahasiswa($nim, "C", $token, $id_smt);
				}
			}
*/
            // $cek_meninggal = 0;
			//cek meninggal
			//if($cek_keluar == 0 && $cek_undir == 0 && $cek_cuti == 0){
				//
				// $cek_db_meninggal = $db->cekMeninggal("STUDENTID='".$nim."' AND SCHOOLYEAR <=".$sc);
				// if (empty($cek_db_meninggal)) {
				// 	$cek_meninggal = 0;
				// } else {
				// 	$cek_db_meninggal = $db->cekMeninggal("STUDENTID='".$nim."' AND SCHOOLYEAR ='".$sc."' AND SEMESTER = ".$smt);
				// 	if (empty($cek_db_meninggal)) {
				// 		$cek_meninggal = 0;
				// 	} else {
				// 		$cek_AKM = $db->getKrs("NIPD='".$nim."' AND SCHOOLYEAR = ".$sc." AND SEMESTER = ".$smt,1,2);
				// 		if (empty($cek_AKM)) {
				// 			$cek_meninggal = 1;
				
				//			TODO PUSH MENINGGAL
							// $akm_util->insertMahasiswaKeluar(6, $nim, $data['id_smt_lulus'], $token, $sc, $smt);
				// 			CEK SKIP REGISTRATION

				// 			TODO CEK NON-AKTIF
				//			$cek_mangkirMhs_smt = $db->getMangkirMhs("STUDENTID='".$nim."' AND SKIPSCHOOLYEAR = ".$sc." AND SKIPSEMESTER = ".$smt,1,2);
							// if(!empty(cek_mangkirMhs_smt))
								// $akm_util->insertAktivitasKuliahMahasiswa($nim, "N", $token, $id_smt);
				// 			echo "PUSH MENINGGAL & NON AKTIF</br>";									
				// 		} else {
				// 			$cek_meninggal = 2;
				// 			//TODO MENINGGAL & PUSH AKTIF
				// 			echo "PUSH MENINGGAL & AKTIF</br>";	
				// 			// $akm_util->insertAktivitasKuliahMahasiswa($nim, "A", $token, $id_smt);
				// 			// $akm_util->insertMahasiswaKeluar(6, $nim, $data['id_smt_lulus'], $token, $sc, $smt);	
				// 		}

				// 	}
				// }
			// }
				
			//Cek_mangkir
			if ($cek_keluar == 0 && $cek_undir == 0 && $cek_cuti == 0 && $cek_meninggal == 0) {

				$cek_mangkirMhs_smt = $db->getMangkirMhs("STUDENTID='".$nim."' AND SKIPSCHOOLYEAR = ".$sc." AND SKIPSEMESTER = ".$smt,1,2);
				
				if (empty($cek_mangkirMhs_smt)) {
					$cek_mangkir = 0;
				} else {
					$cek_mangkir = 1;
				}
				
				// echo "cek mangkir1".$cek_mangkir."</br>";
				//Cek_mangkir
				$cek_mangkirMhs_current_smt = $db->getMangkirMhsCurrent("STUDENTID='".$nim."' and SCHOOLYEAR = ".$sc." and SEMESTER = ".$smt." and REGISTRATIONSTEPID = 3",1,2);

					if (!empty($cek_mangkirMhs_current_smt)) {
						$cek_mangkir = 0;
					} else {
						$cek_mangkir = 1;
					}
				
				// echo "cek mangkir2".$cek_mangkir."</br>";
				/*if ($cek_mangkir == 1) {
					//TODO PUSH MANGKIR/ NON-AKTIF;
					echo "PUSH MANGKIR/ NON-AKTIF</br>";					
					$akm_util->insertAktivitasKuliahMahasiswa($nim, "N", $token, $id_smt);	
				}*/
			}

			/*if ($cek_keluar == 0 && $cek_undir == 0 && $cek_cuti == 0 && $cek_meninggal == 0 && $cek_mangkir == 0) {
				//TODO PUSH AKTIF
				echo "PUSH AKTIF</br>";
				// die();
				$resAkm = $akm_util->insertAktivitasKuliahMahasiswa($nim, "A", $token, $id_smt);
				echo "<pre>";
				print_r($resAkm);
				echo "</pre>";
			}*/
		}
		//die();
	}
	echo "PROCESSING...";
}else{
	echo "SELESAI";
	die();
}

header("Refresh: 3; url=push_keluar.php?id_smt=".$_GET['id_smt']."&row=".($row+$limit)."&push_type=".$pushType);
?>