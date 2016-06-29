<?php
	# service-monitor / https://github.com/aInj/service-monitor/

	$config = array(
		'json'			=>	'SERVER_URL:1337',
		'title'			=>	'Service Status',
		'reload'		=> 	60,
		'timezone'		=>	'Europe/London',
		'db_host'		=>	'',
		'db_username'	=>	'',
		'db_password'	=>	'',
		'db_database'	=>	'',
		'cpy_title'		=>	'My Service',
		'cy_url'		=>	'https://oversight-group.net/'
	);

	try
	{
		$db = new PDO("mysql:host={$config['db_host']};dbname={$config['db_database']};charset=utf8", $config['db_username'], $config['db_password']);
	}
	catch(PDOException $e)
	{
	}

	date_default_timezone_set($config['timezone']);
	$services = json_decode(file_get_contents($config['json']), true);
	$operational = 0;
	foreach($services as $service) if($service['status']) $operational++;
	$operational = count($services) != $operational;
?>
<html>
	<header>
		<title><?php echo $config['title']; ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
		<link href="https://bootswatch.com/yeti/bootstrap.min.css" rel="stylesheet" type="text/css" />
		<script src="https://code.jquery.com/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
		<script>
			var reloadInterval = <?php echo $config['reload']; ?>;
			var timer = setInterval(function() {
				if (reloadInterval > 1) {
					reloadInterval -= 1;
					document.getElementById("reloader").innerHTML = "Reload in " + reloadInterval;
				} else {
					clearInterval(timer);
					location.reload();
				};
			}, 1000);
		</script>
	</header>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<h1><?php echo $config['title']; ?></h1>
				</div>
			</div>
			<div class="row clearfix">
				<div class="col-md-12 column">
					<div class="panel panel-<?php echo $operational ? 'warning' : 'success'; ?>">
						<div class="panel-heading">
						<h3 class="panel-title">
							<?php if($operational) echo 'Not'; ?> All Services Operational
							<small class="pull-right" id="reloader">Reload in <?php echo $config['reload']; ?></small>
						</h3>
						</div>                
					</div>
					<div class="row clearfix">
						<div class="col-md-12">
							<div class="list-group">
							<?php
								foreach($services as $service)
								{
									echo '
									<div class="list-group-item">
										<h4 class="list-group-item-heading">
											'.$service['name'].' 
											<span class="label label-'.($service['status'] ? 'success' : 'danger').'" style="float: right;">'.($service['status'] ? 'Operational' : 'Dysfunctional').'</span>
										</h4>
										<p class="list-group-item-text">
											'.$service['desc'].'
										</p>
									</div>';
								}
							?>
							</div>
						</div>
						<div class="col-md-12">
							<table class="table table-striped">
								<thead>
									<tr>
										<th>ID</th>
										<th>Time</th>
										<th>Service</th>
										<th>Type</th>
										<th>Description</th>
										<th>Last Update</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
								<?php
									$query = $db->prepare('SELECT * FROM `entries` ORDER BY `id` DESC');
									$query->execute();
									$res = $query->fetchAll();
									foreach($res as $row) 
									{
										if($row['status'] == 'hidden') continue;
										echo '
										<tr>
											<td>#'.sprintf("%05d", $row['id']).'</td>
											<td>'.date('F j, Y; H:i:s', $row['time']).'</td>
											<td>'.$row['service'].'</td>
											<td>'.$row['type'].'</td>
											<td>'.$row['description'].'</td>
											<td>'.(!$row['updated'] ? date('F j, Y; H:i:s', $row['updated']) : '-').'</td>
											<td>'.$row['status'].'</td>
										</tr>';
									}
								?>
								</tbody>
							</table>
						</div>
						<div style="text-align: center; color: #999; font-size: 13px;">
							<a href="<?php echo $config['cpy_url']; ?>" target="_blank" style="color: #999;"><?php echo $config['cpy_title']; ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
