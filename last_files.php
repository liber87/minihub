<?php
	if (isset($_POST['dir']))	{
		function glob_tree_files($path, $_base_path = null)
		{
			global $dir;			
			global $time;			
			
			if (is_null($_base_path)) {
				$_base_path = '';
				} else {
				$_base_path .= basename($path) . '/';
			}
			
			$out = array();
			foreach(glob($path . '/*') as $file) {
				if (is_dir($file)) {
					$out = array_merge($out, glob_tree_files($file, $_base_path));
					} else {
					$filepath = $dir .$_base_path . basename($file);					
					if( filemtime($filepath)>$time) $out[] = ['name'=>$filepath,'update'=>date ("d F Y H:i:s", filemtime($filepath))];
				}
			}			
			return $out;
		}
		
		$dir = __DIR__ . $_POST['dir'];		
		$time = strtotime($_POST['date']);
		
		
		$last_files = glob_tree_files($dir);				
	}
	if ((isset($_POST['check_files'])) && (count($_POST['check_files']))){
		$zip = new ZipArchive();		
		$zip->open(__DIR__ . '/archive.zip', ZipArchive::CREATE|ZipArchive::OVERWRITE);
		foreach($_POST['check_files'] as $file){
			$fileName = str_replace(__DIR__,'',$file);
			$zip->addFile($file, $fileName);
		}					
		$zip->close();
		
		$file = __DIR__ . '/archive.zip';
		header('Content-type:  application/zip');
		header('Content-Length: ' . filesize($file));
		header('Content-Disposition: attachment; filename="file.zip"');
		readfile($file);

		ignore_user_abort(true);
		if (connection_aborted()) {
			unlink($file);
		}
		exit();
	}
?>
<!doctype html>
<html lang="ru">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<title>Поиск измененных файлов</title>
		
		
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/air-datepicker/2.2.3/css/datepicker.min.css">
	</head>
	<body>
		<header class="header">
			<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark" role="navigation">
				
			</nav>
		</header>
		<main role="main">
			<div class="jumbotron">
				<div class="container">
					<h1 class="display-3">Поиск измененных файлов</h1>
					<form method="post" action="">
						<div class="row">
							<div class="col-xs-12 col-sm-5">
								<input type="text" name="dir" placeholder="Корневая дериктория поиcка" class="form-control" value="<?=$_POST['dir'];?>">
							</div>
							<div class="col-xs-12 col-sm-5">
								<input type="text" name="date" placeholder="Дата начала" class="form-control datepicker-here" value="<?=$_POST['date'];?>">
							</div>
							<div class="col-xs-12 col-sm-2">
								<button type="submit" class="btn btn-primary" role="button">Искать</button>
							</div>
							
						</div>					
					</form>
				</div>
			</div>
			<div class="container">
				<div class="row">
					<div class="col-md-12">
					<?php
						if (isset($last_files)){
							if (!count($last_files)){
								echo '<div class="alert alert-danger">Файлов измененных зза данный период нет</div>';
							} else {
								echo '<form method="post" action=""><table class="table table-hover"><tr><th width="20px;"><input type="checkbox" id="all"></th><th>Название</th><th>Изменен</th></tr>';
								foreach($last_files as $file){
									$fileName = str_replace(__DIR__,'',$file['name']);
									echo '<tr><td><input type="checkbox" value="'.$file['name'].'" name="check_files[]"></td>
									<td> '.$fileName.'</td><td>'.$file['update'].'</td></tr>';
								}
								echo '</table>
								<p style="text-align:center;"><button type="submit" class="btn btn-success">Сформировать zip архив</button></p>
								</form>';
							}
						}
					?>
					</div>
				</div>
			</div>
		</main>
		<footer role="contentinfo" class="footer">
			<div class="container">
				
			</div>
		</footer>
	</body>
	<style>tr{cursor:pointer;}</style>	
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/air-datepicker/2.2.3/js/datepicker.min.js"></script>
	<script>
		document.addEventListener('DOMContentLoaded', function(){
			$('tr').click(function(){
				let chk = $(this).find('input');
				if (chk.is(':checked')) chk.prop('checked', false);
				else chk.prop('checked', true);
			});
			$('#all').change(function(){
				if ($(this).is(':checked')) $('input[type="checkbox"]').prop('checked', false);
				else $('input[type="checkbox"]').prop('checked', true);
			});
			$('.datepicker-here').datepicker({'timepicker':true});
		});
	</script>
	
</html>