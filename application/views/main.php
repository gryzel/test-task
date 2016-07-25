<!DOCTYPE html>
<html lang="en">

<head>
<?php include($settings['pathViews'].'head.php');?>
</head>

<body>
	<!-- Navigation -->
    <?php include($settings['pathViews'].'navigationTop.php');?>
    
	<!-- Page Content -->
    <div class="container" style="margin-top: 71px;">
			
		
		<!-- Projects Row -->
		<div class="row" style="position:relative">
			<div class="col-md-12 " >
			
				<div class="form-horizontal">
				
					<div class="form-group">
						<label for="stadiums" class="col-sm-2 control-label">Стадіон</label>
						
						<div class="col-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-square"></i></span>
								<?php if($stadiums['status'] === true):?>
								<select id="stadiums" class="form-control">
									<option value="0">Вибрати</option>
									<?php foreach($stadiums['data'] as $key => $stadium):?>
									<option value="<?php echo $stadium['id']?>"><?php echo $stadium['name']?></option>
									<?php endforeach?>
								</select>
								<?php endif?>
							</div>
							
						</div>
					</div>
					
					<div class="form-group">
						<label for="stadiumsSector" class="col-sm-2 control-label">Сектор</label>
						
						<div class="col-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class=" fa fa-square"></i></span>
								<?php if($stadiums['status'] === true):?>
								<select id="stadiumsSector" class="form-control">
									<option value="0">Вибрати</option>
									
								</select>
								<?php endif?>
								
							</div>
						</div>
					</div>
					
					
					
					
				</div>
				
			</div>
			
			
			
		</div>
		<div class="row" style="position:relative">
			<div class="col-md-8 ">
				<h3 id="rowAndSectorName">Сектор <span></span></h3>
			</div>
			<div class="col-md-4 " >
			
			</div>
			
			
			<div class="col-md-8 " id="rowAndSeat">
			
			</div>
			<div class="col-md-4 " id="selectSeat" >
			<div class="">Кількість зарезервованих місць - <span class="countBooked">0</span></div>
				<h3>обрані місця</h3>
				<table class="table table-bordered table-hover table-condensed">
					<thead>
						<tr>
							<th>Стадіон(id)</th>
							<th>Сектор(id)</th>
							<th>Ряд №</th>
							<th>Місце</th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						
					</tbody>
				</table>
				<button type="button" class="btn btn-success" id="booking">Забронювати</button>
			</div>
		</div>
		<div class="row" style="position:relative">
			<div class="col-md-12 " id="rowAndSectorExample">
				
				<table class="table table-bordered table-hover table-condensed">
					<tr>
					  <th>Ряд №</th>
					 
					  <td class="success">Вільно</td>
					  <td class="danger">в процесі бронювання</td>
					  <td class="warning">Обрані міця</td>
					  <td class="active">Заброньовано</td>
					  <td class="info">Заброньовано Вами</td>
					 
					  
					</tr>
				</table>
				
			</div>
		</div>
		
		<div class="row" style="position:relative">
			
			<div class="col-md-12 " id="selectSeatBooked" >
				<h3>Зарезервовані місця</h3>
				<table class="table table-bordered table-hover table-condensed">
					<thead>
						<tr>
							<th>Стадіон(id)</th>
							<th>Сектор(id)</th>
							<th>Ряд №</th>
							<th>Місце</th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						
					</tbody>
				</table>
				
			</div>
		</div>
		<!-- /.row -->
		<?php include($settings['pathViews'].'footer.php');?>

    </div>
    <!-- /.container -->
	
    
    
</body>

</html>
