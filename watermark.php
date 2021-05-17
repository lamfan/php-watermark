<?php 

$files = glob("source/*.{jpg,JPG}", GLOB_BRACE);
$totalImages = count($files);

?>
<!DOCTYPE html>
<html lang="en" class="overflow-hidden">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
     <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">

    <style>
        @keyframes fadeIn {
            0% {opacity:0;transform: scale(.1);}
            100% {opacity:1;transform: scale(1);}
        } 

        html, body {
            max-width: 100%;
        }

        body{
            display:flex;
            flex-wrap:wrap;
            flex-direction:row;
            align-content: flex-start;
        }

        body > .img {
            opacity: 0;
            width:10vw;
            height:7vw;
            animation: fadeIn  1s normal forwards;
            display:flex;
            justify-content: center;
            padding:0.1rem;
        }

        body > .img img {
            max-width:100%;
            height:100%;
        }

        #modal-content {
            height:2rem; 
            border-radius:1rem; 
            line-height:2rem; 
            text-align:right;
            color:white;
            background-color:#007bff;
            width:0%;
            transition: width 1s;
        }

        /* override Bootstrap */
        .modal-open{
            overflow: visible !important;
        }
        .modal-content {
            border-radius: 1rem;
        }
        .modal-backdrop.show {
            opacity: .1;
        }
    </style>
     <!-- JavaScript Bundle with Popper -->
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
        crossorigin="anonymous"></script>
</head>
<body>
    <div class="modal" id="modal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-5">
                <div id="modal-title" class="text-center mb-3"></div>
                <div class="w-100 bg-light overflow-hidden" style="height:2rem; border-radius:1rem;">
                    <div id="modal-content">0%&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var modal = new bootstrap.Modal(document.getElementById('modal'));
        modal.toggle();
        <?php echo "const totalImages = '{$totalImages}';"?>

        function loadedPhoto(filename, param){
            let el = document.createElement('div');
            el.classList.add('img');
            el.innerHTML = `<img src='images/thumb/tm${filename}'>`;
            document.body.appendChild(el);

            let percent = Math.floor((param + 1) / totalImages * 100);
            document.querySelector('#modal-title').innerHTML = `${param + 1} / ${totalImages} 檔案`;
            document.querySelector('#modal-content').innerHTML = `${percent}%&nbsp;&nbsp;`;
            document.querySelector('#modal-content').style.width = `${percent}%`;
            window.scrollTo(0,document.body.scrollHeight);
            if(percent === 100){
                document.querySelector('#modal-content').style.textAlign = "center";
                document.querySelector('#modal-content').innerHTML = "完成所有檔案!";
                modal.toggle();
                document.querySelector('html').classList.remove('overflow-hidden'); 
            }
        }
    </script>
<?php

set_time_limit(0);
ini_set('memory_limit', '-1');

function watermarkImage($filename, $key){
    if (file_exists('images/'.$filename)) {
        createHTML($filename, $key);
        return;
    } 

    $img = imagecreatefromjpeg('source/'.$filename); 
    $watermark = imagecreatefrompng('watermark.png');
    $scale = imagesx($img) / imagesx($watermark);

    $resize = imagescale($watermark, imagesx($watermark)*$scale, imagesy($watermark)*$scale);

    imagecopy($img, $resize, 0, (imagesy($img) - imagesy($resize)) / 2, 0, 0, imagesx($resize), imagesy($resize));
    imagejpeg($img, 'images/'.$filename);
    $thumb = imagescale($img, imagesx($img)/10, imagesy($img)/10);
    imagejpeg($thumb, 'images/thumb/tm'.$filename);
    imagedestroy($thumb);
    imagedestroy($img);
    imagedestroy($watermark);
    imagedestroy($resize);
    createHTML($filename, $key);
}

function createHTML($filename, $id){
    $ms = $id*10;
    echo "<script type='text/javascript'>loadedPhoto('{$filename}', {$id});</script>";
}

// 讀取所有 jpg and JPG 圖檔
foreach ($files as $key => $value) { 
    $filename = $files[$key];
    $filename = str_replace('source/', '', $filename);
    watermarkImage($filename, $key);
    flush();
    ob_flush(); 
}

?>
</body>
</html>