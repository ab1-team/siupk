<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Tanggal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .step-header {
            background-color: #5DC4BF;
            color: white;
            padding: 15px 0;
            width: 100%;
        }
        .step-container {
            display: flex;
            justify-content: center;
            max-width: 600px;
            margin: 0 auto;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            font-weight: bold;
        }
        .step.active {
            background-color: #47B3AE;
        }
        .content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .form-container, .result-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .result-container {
            width: 80%;
            max-width: 600px;
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        label {
            flex: 0 0 80px;
            margin-bottom: 0;
            margin-right: 10px;
            color: #666;
        }
        select, input[type="text"] {
            flex: 1;
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        select {
            appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23007CB2%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 10px top 50%;
            background-size: 12px auto;
        }
        input[type="submit"], button {
            background-color: #5DC4BF;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        input[type="submit"]:hover, button:hover {
            background-color: #47B3AE;
        }
        input[type="submit"]:disabled, button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        .result-text {
            margin-bottom: 20px;
            color: #5DC4BF;
            font-weight: bold;
            font-size: 18px;
        }
        .result-text2 {
            margin-bottom: 20px;
            color: #333;
        }
        .result-text3 {
            margin-bottom: 20px;
            text-align: right;
            margin-top: -15px;
            color: #333;
            font-size: 11px;
        }
        .process-form {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .process-form input[type="text"] {
            width: 70px;
            margin-right: 10px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #5DC4BF;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .back-button {
            background-color: #5DC4BF;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #47B3AE;
        }
    </style>
</head>
<body>
    <header class="step-header">
        <div class="step-container">
            <div class="step active">STEP 1</div>
            <div class="step">STEP 2</div>
            <div class="step">STEP 3</div>
        </div>
    </header>

    <div class="content">
        <?php 
        $koneksi = mysqli_connect('cpanel.siupk.net', 'siupk_global', 'siupk_global', 'siupk_dbm');

        $lokasi = 3;
        $kd_kab = 3;
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if($id == NULL or $id == ""){
                $where = 1;
            } else {
                // Memisahkan ID yang diinput menjadi array
                $id_array = explode(',', $id);
                // Membersihkan setiap ID dari spasi dan memastikan hanya angka
                $id_array = array_map('trim', $id_array);
                $id_array = array_filter($id_array, 'is_numeric');
                // Menggabungkan ID menjadi string untuk query
                $where = "id IN (" . implode(',', $id_array) . ")";
            }
            $q = mysqli_query($koneksi,"SELECT * FROM simpanan_anggota_$kd_kab WHERE ($where) ORDER BY id ASC");
            $total = mysqli_num_rows($q);
            
            echo "<div class='result-container'>";
            echo "<div class='result-text2'>Total Simpanan yang akan di proses adalah " . $total . " data.</div>";
            
            if (isset($_GET['limit']) && isset($_GET['start'])) {
                $start = $_GET['start'] + $_GET['limit'];
                $per_page = 25;
            } else {
                $start = 0;
                $per_page = 25;
            }
            
            ?>
            <form action="" method="get" class="process-form" id="runForm">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="text" name="start" id="start" value="<?php echo $start; ?>" readonly>
                <input type="hidden" name="limit" id="limit" value="<?php echo $per_page; ?>" readonly>
                <button type="submit" name="migrate" id="runButton" <?php echo $start > $total ? 'disabled' : ''; ?>>
                    Loading<span id="loadingDots">.</span>
                </button>
            </form>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var button = document.getElementById('runButton');
                    var loadingDots = document.getElementById('loadingDots');
                    var dotCount = 0;
                
                    function animateDots() {
                        dotCount = (dotCount % 4) + 1;
                        loadingDots.textContent = '.'.repeat(dotCount);
                    }
                
                    if (!button.disabled) {
                        var interval = setInterval(animateDots, 500);
                
                        button.addEventListener('click', function() {
                            clearInterval(interval);
                            button.disabled = true;
                        });
                    }
                });
            </script>
            <?php
            $q2 = mysqli_query($koneksi,"SELECT * FROM simpanan_anggota_$kd_kab WHERE ($where) ORDER BY id ASC  LIMIT $start, $per_page");
            while ($simp = mysqli_fetch_array($q2)) {
                $del_re = mysqli_query($koneksi,"DELETE FROM real_simpanan_$lokasi WHERE cif=$simp[id]");
                $query  = mysqli_query($koneksi,"SELECT * FROM transaksi_$lokasi WHERE id_simp LIKE '$simp[id]' ORDER BY tgl_transaksi ASC, urutan ASC, idt ASC");
                $sum = 0;
                        $str=1;
                while ($trx = mysqli_fetch_array($query)) {
                    $cif            = $simp['id'];
                    $idt            = $trx['idt'];
                    $tgl_transaksi  = $trx['tgl_transaksi'];
                    $jumlah         = $trx['jumlah'];
                    
                    $rdeb           = $trx['rekening_debit'];
                    $rkre           = $trx['rekening_kredit'];

    
                    if(substr($rdeb, 0, 6) == '1.1.01' AND (substr($rkre, 0, 6) == '2.1.05' or substr($rkre, 0, 6) == '2.2.05' ) AND $str==1){  //setor awal
                        $kode = 1;
                        $str=2;
                    }elseif (substr($rdeb, 0, 6) == '1.1.01' AND (substr($rkre, 0, 6) == '2.1.05' or substr($rkre, 0, 6) == '2.2.05' )) {    //setor    
                        $kode = 2;
                    }elseif ((substr($rdeb, 0, 6) == '2.1.05' or substr($rdeb, 0, 6) == '2.2.05' ) AND substr($rkre, 0, 6) == '1.1.01') {    //tarik
                        $kode = 3;
                    }elseif ((substr($rdeb, 0, 6) == '5.2.01' or substr($rdeb, 0, 6) == '5.3.04') AND (substr($rkre, 0, 6) == '2.1.05' or substr($rkre, 0, 6) == '2.2.05' )) {    //bunga
                        $kode = 5;
                    }elseif ((substr($rdeb, 0, 6) == '2.1.05' or substr($rdeb, 0, 6) == '2.2.05' ) AND substr($rkre, 0, 6) == '2.1.03') {    //pajak
                        $kode = 6;
                    }elseif ((substr($rdeb, 0, 6) == '2.1.05' or substr($rdeb, 0, 6) == '2.2.05' ) AND substr($rkre, 0, 6) == '4.1.03') {    //admin
                        $kode = 7;
                    }else{
                        $kode = 0;
                    }
                    if (in_array($kode, ['1', '2', '5'])) {
                        $real_d = 0;
                        $real_k = $jumlah;
                        $sum += $jumlah;
                    } elseif (in_array($kode, ['3', '4', '6', '7'])) {
                        $real_d = $jumlah;
                        $real_k = 0;
                        $sum -= $jumlah;
                    } else {
                        $real_d         = 0;
                        $real_k         = 0;
                    }
                    

                    
                    $lu             = date('Y-m-d H:i:s');
                    $id_user        = $trx['id_user'];
                    
                    $insert_t = mysqli_query($koneksi,"INSERT INTO `real_simpanan_$lokasi`(`cif`, `idt`,`kode`,`tgl_transaksi`, `real_d`, `real_k`, `sum`, `lu`, `id_user`) 
                                 VALUES ('$cif','$idt','$kode','$tgl_transaksi','$real_d','$real_k','$sum','$lu','$id_user')");
                }
            }
            
            if ($start > $total) {
                echo "<div class='result-text'>Proses generate bunga telah selesai</div>";
                echo "<button onclick=\"window.location.href='generate_bunga.php'\" class='back-button'>Next Step</button>";
            }
            echo "</div>";

            if ($start <= $total) {
                echo "
                <script>
                window.onload = function() {
                    setTimeout(function() {
                        document.getElementById('runForm').submit();
                    }, 1000);
                }
                </script>
                ";
            }
        } else {
        ?>
            <div class="form-container">
                <h2>Proses Generate</h2>
                <form>
                    <div class="form-group">
                        <label for="id">CIF</label>
                        <input type="text" id="id" name="id" placeholder="semua CIF">
                    </div>
                    <div class='result-text3'>jika hanya sebagian, ketiklah : <strong>CIF, CIF </strong>dst
                    </div>
                    <input type="submit" value="Kirim">
                </form>
            </div>
        <?php
        }
        ?>
    </div>
</body>
</html>
