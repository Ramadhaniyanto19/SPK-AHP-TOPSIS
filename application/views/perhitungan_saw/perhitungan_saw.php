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


// Bagi setiap bobot kriteria dengan total jumlah kriteria
$normalisasi_element = array(); // Variabel baru untuk menampung matriks hasil normalisasi

// Bagi setiap bobot kriteria dengan total jumlah kriteria
$total_kriteria = count($kriterias);
foreach ($kriterias as $kriteria) {
    $kriteria_id = $kriteria->id_kriteria;
    foreach ($alternatifs as $alternatif) {
        $alternatif_id = $alternatif->id_alternatif;
        $normalisasi_element[$kriteria_id][$alternatif_id] = $matriks_x[$kriteria_id][$alternatif_id] / $total_kriteria;
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

// //total nilai saw
// $total_nilai_saw = 0;
// foreach ($kriterias as $kriteria) {
//     $id_kriteria = $kriteria->id_kriteria;
//     $id_alternatif = $alternatif->id_alternatif;
//     $bobot_ahp = $kriteria->bobot;
//     $nilai_normalisasi = $matriks_normalisasi[$id_kriteria][$id_alternatif];
// 	$nilai_saw = $nilai_normalisasi * $bobot_ahp;
//     $total_nilai_saw += $nilai_saw;
// }
// $total_saw[$alternatif->id_alternatif] = $total_nilai_saw;






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
	<!-- /.card-header -->
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i>Nilai Maximum</h6>
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
		<h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i>Nilai Minimum</h6>
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

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Matriks Normalisasi Setiap Element</h6>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead class="bg-info text-white">
                    <tr align="center">
                        <th width="5%">No</th>
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
                                $nilai_normalisasi_element = $normalisasi_element[$id_kriteria][$id_alternatif];
                            ?>
                                <td><?= $nilai_normalisasi_element ?></td>
                            <?php
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
        <h6 class="m-0 font-weight-bold text-info"><i class="fa fa-table"></i> Hasil Normalisasi Terbobot (R)</h6>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead class="bg-info text-white">
                    <tr align="center">
                        <th width="5%">No</th>
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

                                // Mengalikan hasil normalisasi dengan bobot AHP
                                $nilai_normalisasi = $normalisasi_element[$id_kriteria][$id_alternatif];
                                $bobot_ahp = $kriteria->bobot;
                                $nilai_terbobot = $nilai_normalisasi * $bobot_ahp;
                            ?>
                                <td><?= $nilai_terbobot ?></td>
                            <?php
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
                            $nilai_normalisasi = $normalisasi_element[$id_kriteria][$id_alternatif];
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