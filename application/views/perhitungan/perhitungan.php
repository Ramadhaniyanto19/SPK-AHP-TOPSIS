<?php
$this->load->view('layouts/header_admin');


// Matrix Keputusan (X)
$matriks_x = array();
foreach ($alternatifs as $alternatif) {
    foreach ($kriterias as $kriteria) {
        $id_alternatif = $alternatif->id_alternatif;
        $id_kriteria = $kriteria->id_kriteria;

        $data_pencocokan = $this->Perhitungan_model->data_nilai($id_alternatif, $id_kriteria);
        if (!is_null($data_pencocokan)) {
            $nilai = $data_pencocokan['nilai'];
            $matriks_x[$id_kriteria][$id_alternatif] = $nilai;
        } else {
            $matriks_x[$id_kriteria][$id_alternatif] = 0;
        }
    }
}


$pembagi = array();

// Menghitung pembagi untuk setiap kriteria
foreach ($kriterias as $kriteria) {
    $kriteria_id = $kriteria->id_kriteria;
    $pembagi_kriteria = 0;

    foreach ($alternatifs as $alternatif) {
        $alternatif_id = $alternatif->id_alternatif;
        $nilai = $matriks_x[$kriteria_id][$alternatif_id];
        $pembagi_kriteria += pow($nilai, 2);
    }

    $pembagi_kriteria = sqrt($pembagi_kriteria);
    $pembagi[$kriteria_id] = $pembagi_kriteria;
}


// Menghitung perbandingan normalisasi(R)
$perbandingan_normalisasi = array();

foreach ($alternatifs as $alternatif) {
    $alternatif_id = $alternatif->id_alternatif;
    $perbandingan_normalisasi[$alternatif_id] = array();

    foreach ($kriterias as $kriteria) {
        $kriteria_id = $kriteria->id_kriteria;
        $nilai = $matriks_x[$kriteria_id][$alternatif_id];
        $perbandingan_normalisasi[$alternatif_id][$kriteria_id] = $nilai / $pembagi[$kriteria_id];
    }
}

//Pembobotan Ternormalisasi
$pembobotan_ternormalisasi = array();

foreach ($alternatifs as $alternatif) {
    $alternatif_id = $alternatif->id_alternatif;
    $pembobotan_ternormalisasi[$alternatif_id] = array();

    foreach ($kriterias as $kriteria) {
        $kriteria_id = $kriteria->id_kriteria;
        $nilai = $matriks_x[$kriteria_id][$alternatif_id];
        $normalisasi = $nilai / $pembagi[$kriteria_id];
        $bobot = $kriteria->bobot;
        $hasil = $normalisasi * $bobot;
        $pembobotan_ternormalisasi[$alternatif_id][$kriteria_id] = $hasil;
    }
}


// A+ & A- 
$nilai_max = array();
$nilai_min = array();

// Mencari nilai max dari setiap kriteria
foreach ($kriterias as $kriteria) {
    $kriteria_id = $kriteria->id_kriteria;
    $max = max(array_column($pembobotan_ternormalisasi, $kriteria_id));
    $min = min(array_column($pembobotan_ternormalisasi, $kriteria_id));
    $nilai_max[$kriteria_id] = $max;
    $nilai_min[$kriteria_id] = $min;
}


$jarak_plus = array();
$jarak_min = array();

// Mencari nilai D+ (Jarak Ideal Positif)
foreach ($alternatifs as $alternatif) {
    $alternatif_id = $alternatif->id_alternatif;
    $total_plus = 0;

    foreach ($kriterias as $kriteria) {
        $kriteria_id = $kriteria->id_kriteria;
        $nilai = $pembobotan_ternormalisasi[$alternatif_id][$kriteria_id];
        $max = $nilai_max[$kriteria_id];
        $selisih_plus = $nilai - $max;
        $total_plus += pow($selisih_plus, 2);
    }

    $jarak_plus[$alternatif_id] = sqrt($total_plus);
}

// Mencari nilai D- (Jarak Ideal Negatif)
foreach ($alternatifs as $alternatif) {
    $alternatif_id = $alternatif->id_alternatif;
    $total_min = 0;

    foreach ($kriterias as $kriteria) {
        $kriteria_id = $kriteria->id_kriteria;
        $nilai = $pembobotan_ternormalisasi[$alternatif_id][$kriteria_id];
        $min = $nilai_min[$kriteria_id];
        $selisih_min = $nilai - $min;
        $total_min += pow($selisih_min, 2);
    }

    $jarak_min[$alternatif_id] = sqrt($total_min);
}


$nilai_v = array();

foreach ($alternatifs as $alternatif) {
    $alternatif_id = $alternatif->id_alternatif;
    $s_negatif = $jarak_min[$alternatif_id];
    $s_positif = $jarak_plus[$alternatif_id];
    $v = $s_negatif / ($s_positif + $s_negatif);
    $nilai_v[$alternatif_id] = $v;
}

$kedekatan_relatif = array();

foreach ($alternatifs as $alternatif) {
    $alternatif_id = $alternatif->id_alternatif;
    $nama_alternatif = $alternatif->nama;
    $nilai = $nilai_v[$alternatif_id];

    $kedekatan_relatif[$alternatif_id] = array(
        'id_alternatif' => $alternatif_id,
        'nama' => $nama_alternatif,
        'nilai' => $nilai
    );
}





// foreach ($pembagi as $kriteria_id => $nilai_pembagi) {
//     echo "Pembagi untuk kriteria $kriteria_id: $nilai_pembagi <br>";
// }
// // Menghitung pembagi
// foreach ($kriterias as $kriteria) {
//     $id_kriteria = $kriteria->id_kriteria;
    
//     foreach ($alternatifs as $alternatif) {
//         $id_alternatif = $alternatif->id_alternatif;
        
//         $nilai = $matriks_x[$id_kriteria][$id_alternatif];
        
//         // Menjumlahkan kuadrat setiap bobot sub kriteria dari masing-masing alternatif
//         $pembagi[$id_kriteria] += pow($nilai, 2);
//     }
    
//     // Mengakarkan jumlah kuadrat bobot sub kriteria dari masing-masing alternatif
//     $pembagi[$id_kriteria] = sqrt($pembagi[$id_kriteria]);
// }


// /// Menginisialisasi array untuk matriks x
// $matriks_x = array();

// // Menghitung matriks x berdasarkan matriks u
// foreach ($kriterias as $kriteria) {
//     $id_kriteria = $kriteria->id_kriteria;
//     $bobot_kriteria = $kriteria->bobot;
    
//     foreach ($alternatifs as $alternatif) {
//         $id_alternatif = $alternatif->id_alternatif;
        
//         $nilai_u = $matriks_u[$id_kriteria][$id_alternatif];
        
//         // Menghitung nilai x berdasarkan bobot kriteria
//         $nilai_x = sqrt(pow($nilai_u, 2) * $bobot_kriteria);
        
//         $matriks_x[$id_kriteria][$id_alternatif] = $nilai_x;
//     }
// }

// // Menghitung matriks Y
// $matriks_y = array();
// foreach ($kriterias as $kriteria) {
//     $id_kriteria = $kriteria->id_kriteria;
//     $squares_sum = 0; // Variabel untuk menyimpan jumlah kuadrat setiap elemen matriks X pada kriteria tertentu
    
//     // Menghitung jumlah kuadrat setiap elemen matriks X pada kriteria tertentu
//     foreach ($alternatifs as $alternatif) {
//         $id_alternatif = $alternatif->id_alternatif;
//         $squares_sum += pow($matriks_x[$id_kriteria][$id_alternatif], 2);
//     }
    
//     $sqrt_sum = sqrt($squares_sum); // Menghitung akar kuadrat dari jumlah kuadrat
    
//     // Mengisi matriks Y dengan nilai hasil pembagian setiap elemen matriks X pada kriteria tertentu dengan akar kuadrat
//     foreach ($alternatifs as $alternatif) {
//         $id_alternatif = $alternatif->id_alternatif;
//         $matriks_y[$id_kriteria][$id_alternatif] = $matriks_x[$id_kriteria][$id_alternatif] / $sqrt_sum;
//     }
// }

// // Menghitung matriks D+
// $matriks_d_plus = array();
// foreach ($kriterias as $kriteria) {
//     $id_kriteria = $kriteria->id_kriteria;
//     $max_value = 0; // Variabel untuk menyimpan nilai maksimum setiap kriteria
    
//     // Mencari nilai maksimum dari setiap kriteria
//     foreach ($alternatifs as $alternatif) {
//         $id_alternatif = $alternatif->id_alternatif;
//         if ($matriks_y[$id_kriteria][$id_alternatif] > $max_value) {
//             $max_value = $matriks_y[$id_kriteria][$id_alternatif];
//         }
//     }
    
//     $matriks_d_plus[$id_kriteria] = $max_value;
// }

// // Menghitung matriks D-
// $matriks_d_minus = array();
// foreach ($kriterias as $kriteria) {
//     $id_kriteria = $kriteria->id_kriteria;
//     $min_value = INF; // Variabel untuk menyimpan nilai minimum setiap kriteria
    
//     // Mencari nilai minimum dari setiap kriteria
//     foreach ($alternatifs as $alternatif) {
//         $id_alternatif = $alternatif->id_alternatif;
//         if ($matriks_y[$id_kriteria][$id_alternatif] < $min_value) {
//             $min_value = $matriks_y[$id_kriteria][$id_alternatif];
//         }
//     }
    
//     $matriks_d_minus[$id_kriteria] = $min_value;
// }

// // Menghitung jarak alternatif terhadap solusi ideal positif (S+) dan solusi ideal negatif (S-)
// $jarak_plus = array();
// $jarak_minus = array();
// foreach ($alternatifs as $alternatif) {
//     $id_alternatif = $alternatif->id_alternatif;
//     $sum_plus = 0; // Variabel untuk menyimpan jumlah kuadrat selisih setiap elemen matriks Y pada alternatif tertentu dengan S+
//     $sum_minus = 0; // Variabel untuk menyimpan jumlah kuadrat selisih setiap elemen matriks Y pada alternatif tertentu dengan S-
    
//     foreach ($kriterias as $kriteria) {
//         $id_kriteria = $kriteria->id_kriteria;
//         $selisih_plus = $matriks_y[$id_kriteria][$id_alternatif] - $matriks_d_plus[$id_kriteria];
//         $selisih_minus = $matriks_y[$id_kriteria][$id_alternatif] - $matriks_d_minus[$id_kriteria];
//         $sum_plus += pow($selisih_plus, 2);
//         $sum_minus += pow($selisih_minus, 2);
//     }
    
//     $jarak_plus[$id_alternatif] = sqrt($sum_plus);
//     $jarak_minus[$id_alternatif] = sqrt($sum_minus);
// }

// // Menghitung nilai preferensi (V) untuk setiap alternatif
// $nilai_preferensi = array();
// foreach ($alternatifs as $alternatif) {
//     $id_alternatif = $alternatif->id_alternatif;
//     $nilai_preferensi[$id_alternatif] = array(
//         'nama' => $alternatif->nama, // Menyimpan nama alternatif
//         'id_alternatif' => $alternatif->id_alternatif, // Menyimpan nama alternatif
//         'nilai' => $jarak_minus[$id_alternatif] / ($jarak_minus[$id_alternatif] + $jarak_plus[$id_alternatif]) // Menyimpan nilai preferensi (V)
//     );
// }

?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-calculator"></i> Data Perhitungan</h1>
</div>




<div class="card shadow mb-4">
	<!-- /.card-header -->
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Matrix Keputusan (X)</h6>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<th width="5%" rowspan="2">No</th>
						<th>Nama Alternatif</th>
						<?php foreach ($kriterias as $kriteria) : ?>
							<th><?= $kriteria->kode_kriteria ?></th>
						<?php endforeach ?>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = 1;
					foreach ($alternatifs as $alternatif) : ?>
						<tr align="center">
							<td><?= $no; ?></td>
							<td align="left"><?= $alternatif->nama ?></td>
							<?php
							foreach ($kriterias as $kriteria) :
								$id_alternatif = $alternatif->id_alternatif;
								$id_kriteria = $kriteria->id_kriteria;
								echo '<td>';
								echo $matriks_x[$id_kriteria][$id_alternatif];
								echo '</td>';
							endforeach
							?>
						</tr>
					<?php
						$no++;
					endforeach
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>


<div class="card shadow mb-4">
    <!-- /.card-header -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Pembagi</h6>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead class="bg-info text-white">
                    <tr align="center">
                        <?php foreach ($kriterias as $kriteria) : ?>
                            <th><?= $kriteria->kode_kriteria ?> (<?= $kriteria->jenis ?>)</th>
                        <?php endforeach ?>
                    </tr>
                </thead>
                <tbody>
                    <tr align="center">
                        <?php
                        foreach ($kriterias as $kriteria) {
                            $pembagi = 0;
                            foreach ($alternatifs as $alternatif) {
                                $id_alternatif = $alternatif->id_alternatif;
                                $id_kriteria = $kriteria->id_kriteria;
                                $pembagi += pow($matriks_x[$id_kriteria][$id_alternatif], 2);
                            }
                            $pembagi = sqrt($pembagi);
                            echo "<td>$pembagi</td>";
                        }
                        ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="card shadow mb-4">
	<!-- /.card-header -->
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Bobot Kriteria (W)</h6>
	</div>

	<div class="card-body">
		<div class="alert alert-danger text-justify">
			Bobot kriteria didapatkan dari perhitungan menggunakan metode <b>AHP</b>. Silahkan menuju ke halaman <a href="<?= base_url('') ?>Kriteria/prioritas" class="btn btn-info">Kriteria</a> untuk melihat proses perhitungan.
		</div>
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<?php foreach ($kriterias as $kriteria) : ?>
							<th><?= $kriteria->kode_kriteria ?> (<?= $kriteria->jenis ?>)</th>
						<?php endforeach ?>
					</tr>
				</thead>
				<tbody>
					<tr align="center">
						<?php foreach ($kriterias as $kriteria) : ?>
							<td>
								<?php
								echo $kriteria->bobot;
								?>
							</td>
						<?php endforeach ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Matriks Ternormalisasi (R)</h6>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<th width="5%" rowspan="2">No</th>
						<th>Nama Alternatif</th>
						<?php foreach ($kriterias as $kriteria) : ?>
							<th><?= $kriteria->kode_kriteria ?></th>
						<?php endforeach ?>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = 1;
					foreach ($alternatifs as $alternatif) : ?>
						<tr align="center">
							<td><?= $no; ?></td>
							<td align="left"><?= $alternatif->nama ?></td>
							<?php
							foreach ($kriterias as $kriteria) :
								$id_alternatif = $alternatif->id_alternatif;
								$id_kriteria = $kriteria->id_kriteria;
								echo '<td>';
								echo $perbandingan_normalisasi[$id_alternatif][$id_kriteria];
								echo '</td>';
							endforeach;
							?>
						</tr>
					<?php
						$no++;
					endforeach;
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>




<!-- <div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Matriks Ternormalisasi (R)</h6>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<th width="5%" rowspan="2">No</th>
						<th>Nama Alternatif</th>
						<?php foreach ($kriterias as $kriteria) : ?>
							<th><?= $kriteria->kode_kriteria ?></th>
						<?php endforeach ?>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = 1;
					foreach ($alternatifs as $alternatif) : ?>
						<tr align="center">
							<td><?= $no; ?></td>
							<td align="left"><?= $alternatif->nama ?></td>
							<?php
							foreach ($kriterias as $kriteria) :
								$id_alternatif = $alternatif->id_alternatif;
								$id_kriteria = $kriteria->id_kriteria;
								echo '<td>';
								echo $matriks_x[$id_kriteria][$id_alternatif];
								echo '</td>';
							endforeach;
							?>
						</tr>
					<?php
						$no++;
					endforeach
					?>
				</tbody>
			</table>
		</div>
	</div>
</div> -->


<div class="card shadow mb-4">
	<!-- /.card-header -->
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Pembobotan Ternormalisasi</h6>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<th width="5%" rowspan="2">No</th>
						<th>Nama Alternatif</th>
						<?php foreach ($kriterias as $kriteria) : ?>
							<th><?= $kriteria->kode_kriteria ?></th>
						<?php endforeach ?>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = 1;
					foreach ($alternatifs as $alternatif) : ?>
						<tr align="center">
							<td><?= $no; ?></td>
							<td align="left"><?= $alternatif->nama ?></td>
							<?php
							foreach ($kriterias as $kriteria) :
								$id_alternatif = $alternatif->id_alternatif;
								$id_kriteria = $kriteria->id_kriteria;
								$hasil_normalisasi = $pembobotan_ternormalisasi[$id_alternatif][$id_kriteria];
								echo '<td>';
								echo $hasil_normalisasi;
								echo '</td>';
							endforeach;
							?>
						</tr>
					<?php
						$no++;
					endforeach
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>


<!-- <div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Matriks Y</h6>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<th width="5%" rowspan="2">No</th>
						<th>Nama Alternatif</th>
						<?php foreach ($kriterias as $kriteria) : ?>
							<th><?= $kriteria->kode_kriteria ?></th>
						<?php endforeach ?>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = 1;
					foreach ($alternatifs as $alternatif) : ?>
						<tr align="center">
							<td><?= $no; ?></td>
							<td align="left"><?= $alternatif->nama ?></td>
							<?php
							foreach ($kriterias as $kriteria) :
								$id_alternatif = $alternatif->id_alternatif;
								$id_kriteria = $kriteria->id_kriteria;
								echo '<td>';
								echo $matriks_y[$id_kriteria][$id_alternatif];
								echo '</td>';
							endforeach;
							?>
						</tr>
					<?php
						$no++;
					endforeach
					?>
				</tbody>
			</table>
		</div>
	</div>
</div> -->

<!-- <div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Solusi Ideal Positif (A+)</h6>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<?php foreach ($kriterias as $kriteria) : ?>
							<th><?php echo $kriteria->keterangan; ?> (<?php echo $kriteria->kode_kriteria; ?>)</th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<tr align="center">
						<?php foreach ($kriterias as $kriteria) : ?>
							<td>
								<?php
								$id_kriteria = $kriteria->id_kriteria;
								echo $matriks_d_plus[$id_kriteria];
								?>
							</td>
						<?php endforeach; ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div> -->

<div class="card shadow mb-4">
	<!-- /.card-header -->
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Solusi Ideal Positif (A+)</h6>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<?php foreach ($kriterias as $kriteria) : ?>
							<th><?php echo $kriteria->keterangan; ?> (<?php echo $kriteria->kode_kriteria; ?>)</th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<tr align="center">
						<?php foreach ($kriterias as $kriteria) : ?>
							<td>
								<?php
								$id_kriteria = $kriteria->id_kriteria;
								echo $nilai_max[$id_kriteria];
								?>
							</td>
						<?php endforeach; ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="card shadow mb-4">
	<!-- /.card-header -->
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Solusi Ideal Negatif (A-)</h6>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<?php foreach ($kriterias as $kriteria) : ?>
							<th><?php echo $kriteria->keterangan; ?> (<?php echo $kriteria->kode_kriteria; ?>)</th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<tr align="center">
						<?php foreach ($kriterias as $kriteria) : ?>
							<td>
								<?php
								$id_kriteria = $kriteria->id_kriteria;
								echo $nilai_min[$id_kriteria];
								?>
							</td>
						<?php endforeach; ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- <div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Solusi Ideal Negatif (A-)</h6>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<?php foreach ($kriterias as $kriteria) : ?>
							<th><?php echo $kriteria->keterangan; ?> (<?php echo $kriteria->kode_kriteria; ?>)</th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<tr align="center">
						<?php foreach ($kriterias as $kriteria) : ?>
							<td>
								<?php
								$id_kriteria = $kriteria->id_kriteria;
								echo $matriks_d_minus[$id_kriteria];
								?>
							</td>
						<?php endforeach; ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div> -->

<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Jarak Ideal Positif (D+)</h6>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<th width="5%">No</th>
						<th>Nama Alternatif</th>
						<th width="30%">Jarak Ideal Positif</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = 1;
					foreach ($alternatifs as $alternatif) : ?>
						<tr align="center">
							<td><?php echo $no; ?></td>
							<td align="left"><?php echo $alternatif->nama; ?></td>
							<td>
								<?php
								$id_alternatif = $alternatif->id_alternatif;
								echo $jarak_plus[$id_alternatif];
								?>
							</td>
						</tr>
					<?php
						$no++;
					endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>


<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Jarak Ideal Negatif (D-)</h6>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<th width="5%">No</th>
						<th>Nama Alternatif</th>
						<th width="30%">Jarak Ideal Negatif</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = 1;
					foreach ($alternatifs as $alternatif) : ?>
						<tr align="center">
							<td><?php echo $no; ?></td>
							<td align="left"><?php echo $alternatif->nama; ?></td>
							<td>
								<?php
								$id_alternatif = $alternatif->id_alternatif;
								echo $jarak_min[$id_alternatif];
								?>
							</td>
						</tr>
					<?php
						$no++;
					endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>


<div class="card shadow mb-4">
	<!-- /.card-header -->
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Kedekatan Relatif Terhadap Solusi Ideal (V)</h6>
	</div>

	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered" width="100%" cellspacing="0">
				<thead class="bg-info text-white">
					<tr align="center">
						<th width="5%">No</th>
						<th>Nama Alternatif</th>
						<th width="30%">Nilai</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = 1;
					$this->Perhitungan_model->hapus_hasil();
					foreach ($kedekatan_relatif as $alternatif) : ?>
						<tr align="center">
							<td><?php echo $no; ?></td>
							<td align="left"><?php echo $alternatif['nama']; ?></td>
							<td><?php echo $alternatif['nilai']; ?></td>
						</tr>
					<?php
						$no++;
						$hasil_akhir = [
							'id_alternatif' => $alternatif['id_alternatif'],
							'nilai' => $alternatif['nilai']
						];
						$this->Perhitungan_model->insert_hasil($hasil_akhir);
					endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php
$this->load->view('layouts/footer_admin');
?>