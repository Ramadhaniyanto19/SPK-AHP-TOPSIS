<?php
$this->load->view('layouts/header_admin');


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

// Mencari nilai maksimum dan minimum pada setiap kriteria
$nilai_max = array();
$nilai_min = array();
foreach ($kriterias as $kriteria) {
    $id_kriteria = $kriteria->id_kriteria;
    $nilai_max[$id_kriteria] = max($matriks_x[$id_kriteria]);
    $nilai_min[$id_kriteria] = min($matriks_x[$id_kriteria]);
}

// Normalisasi matriks X menggunakan metode SAW
$matriks_normalisasi = array();
foreach ($kriterias as $kriteria) {
    $id_kriteria = $kriteria->id_kriteria;
    foreach ($alternatifs as $alternatif) {
        $id_alternatif = $alternatif->id_alternatif;
        $nilai = $matriks_x[$id_kriteria][$id_alternatif];

        // Normalisasi menggunakan rumus (nilai - nilai minimum) / (nilai maksimum - nilai minimum)
        $nilai_normalisasi = ($nilai - $nilai_min[$id_kriteria]) / ($nilai_max[$id_kriteria] - $nilai_min[$id_kriteria]);

        $matriks_normalisasi[$id_kriteria][$id_alternatif] = $nilai_normalisasi;
    }
}

//total nilai saw
$total_nilai_saw = 0;
foreach ($kriterias as $kriteria) {
    $id_kriteria = $kriteria->id_kriteria;
    $id_alternatif = $alternatif->id_alternatif;
    $bobot_ahp = $kriteria->bobot;
    $nilai_normalisasi = $matriks_normalisasi[$id_kriteria][$id_alternatif];
	$nilai_saw = $nilai_normalisasi * $bobot_ahp;
    $total_nilai_saw += $nilai_saw;
}
$total_saw[$alternatif->id_alternatif] = $total_nilai_saw;



// //Matrix Keputusan (X)
// $matriks_x = array();
// foreach ($alternatifs as $alternatif) :
// 	foreach ($kriterias as $kriteria) :

// 		$id_alternatif = $alternatif->id_alternatif;
// 		$id_kriteria = $kriteria->id_kriteria;

// 		$data_pencocokan = $this->Perhitungan_model->data_nilai($id_alternatif, $id_kriteria);
// 		if (!is_null($data_pencocokan)) {
// 			$nilai = $data_pencocokan['nilai'];
// 			$matriks_x[$id_kriteria][$id_alternatif] = $nilai;
// 		} else {

// 			$matriks_x[$id_kriteria][$id_alternatif] = 0;
// 		}
// 	endforeach;
// endforeach;

// //Matriks Ternormalisasi (R)
// $matriks_r = array();
// foreach ($matriks_x as $id_kriteria => $penilaians) :

// 	$jumlah_kuadrat = 0;
// 	foreach ($penilaians as $penilaian) :
// 		$jumlah_kuadrat += pow($penilaian, 2);
// 	endforeach;
// 	$akar_kuadrat = sqrt($jumlah_kuadrat);

// 	foreach ($penilaians as $id_alternatif => $penilaian) :
// 		$matriks_r[$id_kriteria][$id_alternatif] = $penilaian / $akar_kuadrat;
// 	endforeach;

// endforeach;

// //Matriks Y
// $matriks_y = array();
// foreach ($alternatifs as $alternatif) :
// 	foreach ($kriterias as $kriteria) :

// 		$bobot = $kriteria->bobot;
// 		$id_alternatif = $alternatif->id_alternatif;
// 		$id_kriteria = $kriteria->id_kriteria;

// 		$nilai_r = $matriks_r[$id_kriteria][$id_alternatif];
// 		$matriks_y[$id_kriteria][$id_alternatif] = $bobot * $nilai_r;

// 	endforeach;
// endforeach;

// $hasil_saw = array();
// foreach ($alternatifs as $alternatif) {
//     $id_alternatif = $alternatif->id_alternatif;
//     $total_nilai = 0;
    
//     foreach ($kriterias as $kriteria) {
//         $id_kriteria = $kriteria->id_kriteria;
        
//         // Mengambil nilai bobot dari kriteria
//         $bobot = $kriteria->bobot;
        
//         // Mengambil nilai ternormalisasi dari matriks Y
//         $nilai_y = $matriks_y[$id_kriteria][$id_alternatif];
        
//         // Menghitung nilai SAW
//         $total_nilai += $bobot * $nilai_y;
//     }
    
//     // $hasil_saw[$id_alternatif]['id_alternatif'] = $total_nilai;
// 	$hasil_saw[$id_alternatif]['id_alternatif'] = $id_alternatif;
//     $hasil_saw[$id_alternatif]['nama'] = $alternatif->nama;
//     $hasil_saw[$id_alternatif]['nilai'] = $total_nilai;
// 	// $hasil_saw[$alternatif->id_alternatif]['id_alternatif'] = $alternatif->id_alternatif;
// 	// $hasil_saw[$alternatif->id_alternatif]['nama'] = $alternatif->nama;
// 	// $hasil_saw[$alternatif->id_alternatif]['nilai'] = $nilai_v;

// }



?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-calculator"></i> Data Perhitungan</h1>
</div>

<div class="card shadow mb-4">
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
					foreach ($alternatifs as $alternatif) :
					?>
						<tr align="center">
							<td><?= $no; ?></td>
							<td align="left"><?= $alternatif->nama ?></td>
							<?php
							foreach ($kriterias as $kriteria) :
								$id_alternatif = $alternatif->id_alternatif;
								$id_kriteria = $kriteria->id_kriteria;
								echo '<td>';

								// Mengalikan hasil normalisasi dengan bobot AHP
								$nilai_normalisasi = $matriks_normalisasi[$id_kriteria][$id_alternatif];
								$bobot_ahp = $kriteria->bobot;
								$nilai_saw = $nilai_normalisasi * $bobot_ahp;

								echo $nilai_saw;

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

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i>Perhitungan (V)</h6>
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
                    $this->Perhitungan_model->hapus_hasil_saw();
                    $hasil_saw = array();

                    foreach ($alternatifs as $alternatif) :
                        $total_nilai_saw = 0;
                        foreach ($kriterias as $kriteria) :
                            $id_kriteria = $kriteria->id_kriteria;
                            $id_alternatif = $alternatif->id_alternatif;
                            $bobot_ahp = $kriteria->bobot;
                            $nilai_normalisasi = $matriks_normalisasi[$id_kriteria][$id_alternatif];
                            $nilai_saw = $nilai_normalisasi * $bobot_ahp;
                            $total_nilai_saw += $nilai_saw;
						endforeach;
                        $hasil_saw[] = array(
                            'id_alternatif' => $alternatif->id_alternatif,
                            'nama' => $alternatif->nama,
                            'nilai' => $total_nilai_saw
                        );
                    endforeach;

                    // Urutkan hasil SAW secara descending berdasarkan nilai
                    usort($hasil_saw, function ($a, $b) {
                        return $b['nilai'] <=> $a['nilai'];
                    });

                    foreach ($hasil_saw as $alternatif) :
                    ?>
                        <tr align="center">
                            <td><?php echo $no; ?></td>
                            <td align="left"><?php echo $alternatif['nama']; ?></td>
                            <td><?php echo $alternatif['nilai']; ?></td>
                        </tr>
                    <?php
                        $no++;
                        $hasil_akhir_saw = [
                            'id_alternatif' => $alternatif['id_alternatif'],
                            'nilai' => $alternatif['nilai']
                        ];
                        $this->Perhitungan_model->insert_hasil_saw($hasil_akhir_saw);
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


</div>




<?php
$this->load->view('layouts/footer_admin');
?>