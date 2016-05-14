<?php

require_once 'utils/config.php';
require_once 'utils/PodioAPI.php';
$success = null;
date_default_timezone_set("Asia/Jerusalem");


Podio::setup(CLIENT_ID, CLIENT_SECRET, array(
	"session_manager" => "PodioBrowserSession"
));

if(!Podio::is_authenticated()) {header("location: index.php"); exit;}

if($_GET["userid"] == 1) {
	header('Content-Type: application/json');
	die('{"res":true}');
}

if($_POST["project"]) {
//	print_r($_POST);
	if($_POST["_id"]) {
		$item = PodioItem::get_basic((int)$_POST["_id"]); // Get item with item_id=123
		$item->fields["time-spent"] = new PodioTextItemField(array("external_id" => "time-spent", "values" => (int)$_POST["time"]));
		$item->fields["details-of-work"] = new PodioTextItemField(array("external_id" => "details-of-work", "values" => $_POST["description"]));
		$item->fields["hrkt-shvt-lmshymh"] = new PodioTextItemField(array("external_id" => "hrkt-shvt-lmshymh", "values" => $est));
		$item->fields["syvvg"] = new PodioTextItemField(array("external_id" => "syvvg", "values" => (int)$_POST["sivug"]));
		$item->fields["tkvlh"] = new PodioTextItemField(array("external_id" => "tkvlh", "values" => (int)$_POST["joomla"]));
		$item->fields["tkvlh-mgntv"] = new PodioTextItemField(array("external_id" => "tkvlh-mgntv", "values" => (int)$_POST["magento"]));
		try {
		$save = $item->save();
			header("location: form.php?s=1");
		} catch (Exception $e) {
			header("location: form.php?s=2&".$e);
		}

	} else {
		$contact = PodioContact::get_for_user(PodioUser::get()->id);
		$est = ((int)$_POST["est_hours"] * 60 + (int)$_POST["est_min"]) * 60;
		$fields = new PodioItemFieldCollection(array(
			new PodioDateItemField(array("external_id" => "date", "values" => array('start' => date("Y-m-d", strtotime("now"))))),      //date
			new PodioContactItemField(array("external_id" => "ysh-tsvvt", "values" => array('profile_id' => $contact->profile_id))),    //contact
			new PodioTextItemField(array("external_id" => "time-spent", "values" => (int)$_POST["time"])),              //time spent duration
			new PodioTextItemField(array("external_id" => "hrkt-shvt-lmshymh", "values" => $est)),         				//time astimate
			new PodioTextItemField(array("external_id" => "shyvk-lprvyqt", "values" => (int)$_POST["project"])),       	//project
			new PodioTextItemField(array("external_id" => "syvvg", "values" => (int)$_POST["sivug"])),                  //sivug
			new PodioTextItemField(array("external_id" => "tkvlh", "values" => (int)$_POST["joomla"])),       			//joomla
			new PodioTextItemField(array("external_id" => "tkvlh-mgntv", "values" => (int)$_POST["magento"])),          //magento
			new PodioTextItemField(array("external_id" => "details-of-work", "values" => $_POST["description"])),
			new PodioTextItemField(array("external_id" => "start", "values" => array('start' => date("Y-m-d H:i:s", $_POST["start"])))),
			new PodioTextItemField(array("external_id" => "submit", "values" => array('start' => date("Y-m-d H:i:s", strtotime("now")))))
		));

		$item = new PodioItem(array(
			'app' => new PodioApp(APP_ID), // Attach to app with app_id=123
			'fields' => $fields
		));

		try {
			$save = $item->save();

			if ($save->item_id) {
				$success = true;
				header("location: form.php?s=1");
			} else {
				$success = false;
				header("location: form.php?s=2");
			}
		} catch (Exception $e) {
//		header("location: form.php?s=2");
			print_r($e);
		}
	}
}

// ana 719678
// tali 2968146
// shay 605723

//session_start();
//if(!$_SESSION["projectList"]) {

/*
 * GET CLOCK FOR TODAY
 */

if($_POST["start_day"] == 9) {
	$fields = new PodioItemFieldCollection(array(
		new PodioTextItemField(array("external_id" => "date", "values" => array('start' => date("Y-m-d H:i:s", strtotime("now")))))
	));

	$item = new PodioItem(array(
		'app' => new PodioApp(CLOCK_APP_ID), // Attach to app with app_id=123
		'fields' => $fields
	));

	try {
		$save = $item->save();

		if ($save->item_id) {
			$success = true;
			header("location: form.php");
		} else {
			$success = false;
			header("location: form.php");
		}
	} catch (Exception $e) {
//		header("location: form.php?s=2");
		print_r($e);
	}
}

if($_POST["end_day"] == 9) {
	$item = PodioItem::get_basic((int)$_COOKIE["PODIOID"]); // Get item with item_id=123
	$item->fields["end"] = new PodioTextItemField(array("external_id" => "end", "values" => array('start' => date("Y-m-d H:i:s", strtotime("now")))));
	try {
		$save = $item->save();
		unset($_COOKIE['PODIOST']);
		setcookie('PODIOST', null, -1, '/');

		header("location: form.php?endday=1");
	} catch (Exception $e) {
		echo $e;
	}
}

$day_started = false;
$day_started_at = $_COOKIE["PODIOST"];
if(!$day_started_at) {
	$reported_time = PodioItem::filter(CLOCK_APP_ID,
		array('filters' =>
			array(
				'created_by' => array('type' => 'user', 'id' => PodioUser::get()->id),
				'created_on' => array('from' => date("Y-m-d", strtotime("now")))
			)));

	if (count($reported_time) > 1)
		die("Too many time reports for today!!");
	elseif (count($reported_time) == 1) {
		foreach ($reported_time as $itemOne) {
			$itemid = $itemOne->item_id;
			foreach ($itemOne->fields as $field)
				if ($field->external_id == "end") {
					if($field->values["start"]){
						unset($_COOKIE['PODIOST']);
						setcookie('PODIOST', null, -1, '/');
						$day_started = false;
						break;
					}
				}
				if ($field->external_id == "date") {
					$date = $field->values["start"];
					$date->setTimezone(new DateTimeZone('Asia/Jerusalem'));
					$day_started_at = date_format($date, 'd-m-Y H:i:s');
					setcookie("PODIOST", $day_started_at, time() + (3600 * 12), "/"); // 86400 = 1 day
					setcookie("PODIOID", $itemid, time() + (3600 * 12), "/"); // 86400 = 1 day
					$day_started = true;
				}
		}

	} else {
		$day_started = false;
	}
} else {
	$day_started = true;
}





//reported hours today
$itemsColl = PodioItem::filter(APP_ID,
	array('filters' =>
		array(
			 'created_by' => array('type' => 'user', 'id' => PodioUser::get()->id),
			 'created_on' => array('from' => date("Y-m-d", strtotime("now")))
		)));

$reported = array();
$total = 0;
foreach($itemsColl as $itemOne) {
//	print_r($itemOne);exit;
	$reportedVal = array();
	$reportedVal["_id"] = $itemOne->item_id;
	foreach($itemOne->fields as $ind=>$field){
		if($field->external_id == 'time-spent'){
			$reportedVal["time"] = $field->values;
			$total += $field->values;
		} elseif($field->external_id == 'shyvk-lprvyqt'){
			foreach($field->values as $field){
				$reportedVal["title"] = $field->title;
				$reportedVal["item_id"] = $field->item_id;
			}
		} elseif($field->external_id == 'hrkt-shvt-lmshymh'){
			$reportedVal["time-spent"] = $field->values;
		} elseif($field->external_id == 'syvvg'){
			$reportedVal["syvvg"] = $field->values;
		} elseif($field->external_id == 'tkvlh'){
			$reportedVal["tkvlh"] = $field->values;
		} elseif($field->external_id == 'tkvlh-mgntv'){
			$reportedVal["tkvlh-mgntv"] = $field->values;
		} elseif($field->external_id == 'details-of-work'){
			$reportedVal["details-of-work"] = $field->values;
		}

	}
	$reported[] = $reportedVal;
}

//if(PodioUser::get()->id == 719578 || /*PodioUser::get()->id == 605723 ||*/ PodioUser::get()->id == 2968146){
	$supp = json_decode('{"target":"item_field","text":"מעטפת זהב","limit":50,"target_params":{"field_id":46737851,"not_item_ids":[]}}');
	$supp_items1 = PodioReference::search($supp);
	$suppArr = array();

	foreach ($supp_items1[0]["contents"] as $suppItem) {
		if(strstr($suppItem["title"], "לא פעיל")) continue;
		$suppArr[] = array("id" => $suppItem["item_id"], "name" => $suppItem["title"]);
	}

	$supp2 = json_decode('{"target":"item_field","text":"תחזוקה","limit":50,"target_params":{"field_id":46737851,"not_item_ids":[]}}');
	$supp_items2 = PodioReference::search($supp2);
	$suppArr2 = array();
	if($supp_items2[0]["contents"])
	foreach ($supp_items2[0]["contents"] as $suppItem) {
		if(strstr($suppItem["title"], "לא פעיל")) continue;
		$suppArr2[] = array("id" => $suppItem["item_id"], "name" => $suppItem["title"]);
	}

	$supp3 = json_decode('{"target":"item_field","text":"אחריות","limit":50,"target_params":{"field_id":46737851,"not_item_ids":[]}}');
	$supp_items3 = PodioReference::search($supp3);
	$suppArr3 = array();

	if($supp_items3[0]["contents"])
	foreach ($supp_items3[0]["contents"] as $suppItem) {
		if(strstr($suppItem["title"], "לא פעיל")) continue;
		$suppArr3[] = array("id" => $suppItem["item_id"], "name" => $suppItem["title"]);
	}

	$supp4 = json_decode('{"target":"item_field","text":"בנק שעות","limit":50,"target_params":{"field_id":46737851,"not_item_ids":[]}}');
	$supp_items4 = PodioReference::search($supp4);
	$suppArr4 = array();

	if($supp_items4[0]["contents"])
	foreach ($supp_items4[0]["contents"] as $suppItem) {
		if(strstr($suppItem["title"], "לא פעיל")) continue;
		$suppArr4[] = array("id" => $suppItem["item_id"], "name" => $suppItem["title"]);
	}

	$supp5 = json_decode('{"target":"item_field","text":"בעבודה","limit":50,"target_params":{"field_id":46737851,"not_item_ids":[]}}');
	$supp_items5 = PodioReference::search($supp5);
	$suppArr5 = array();

	if($supp_items5[0]["contents"])
	foreach ($supp_items5[0]["contents"] as $suppItem) {
		if(strstr($suppItem["title"], "לא פעיל")) continue;
		$suppArr5[] = array("id" => $suppItem["item_id"], "name" => $suppItem["title"]);
	}

	$supp6 = json_decode('{"target":"item_field","text":"תקופת אחראיות","limit":50,"target_params":{"field_id":46737851,"not_item_ids":[]}}');
	$supp_items6 = PodioReference::search($supp6);
	$suppArr6 = array();

	if($supp_items6[0]["contents"])
	foreach ($supp_items6[0]["contents"] as $suppItem) {
		if(strstr($suppItem["title"], "לא פעיל")) continue;
		$suppArr6[] = array("id" => $suppItem["item_id"], "name" => $suppItem["title"]);
	}
//}
//	$_SESSION["projectList"] = $projArr;
//} else {
//	$projArr = $_SESSION["projectList"];
//}

if($query = $_GET["query"]) {
	$proj = json_decode('{"target":"item_field","text":"'.$query.'","limit":50,"target_params":{"field_id":46737851,"not_item_ids":[]}}');
	$proj_itemsX = PodioReference::search($proj);
	$projArr = array();

	foreach ($proj_itemsX[0]["contents"] as $projItem) {
		$projArr[] = array("id" => $projItem["item_id"], "name" => $projItem["title"]);
	}

	header('Content-Type: application/json');
	echo json_encode($projArr);exit;
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Joomi time manager</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="shortcut icon" type="image/png" href="favicon32.ico">
	<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="js/jquery-ui-1.10.4.custom.min.css" />

	<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="bootstrap/js/bootbox.min.js"></script>
	<script type="text/javascript" src="js/favico.js"></script>
	<script type="text/javascript" src="js/timer.js"></script>
	<script type="text/javascript" src="js/autocomplete.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.10.4.custom.min.js"></script>
</head>
<body>

	<?php if($_GET["endday"] == 1){?>
	<div class="container">
		<div class="page-header row">
			<h1>See u Tommorow {: </h1>
		</div>
	</div>
	<?php } else {?>
	<div class="container">
		<div class="page-header row">
			<h1>Joomi Task timer <small>Easily add time track to Podio</small>
				<?php if($day_started == true){?>
					<form style="display: inline;" method="post" id="submitend">
						<input type="hidden" name="end_day" value="9">
						<button class="btn btn-danger" type="button" id="endday">END</button>
					</form>
				<?php } else { ?>
					<form style="display: inline;" method="post">
						<input type="hidden" name="start_day" value="9">
						<button class="btn-lg btn-success" type="submit">start</button>
					</form>
				<?php } ?>

			</h1>
		</div>


		<div class="row">
			<div class="col-lg-5 col-md-push-1">
				<div class="col-md-12">
					<?php if($_GET["s"] == 1){?>
					<div class="alert alert-success">
						<strong><span class="glyphicon glyphicon-ok"></span> Success! <br> <span style="direction: rtl"></span> דיווח שעות נשמר בהצלחה - קדימה למשימה הבאה {: </span></strong>
					</div>
						<script>
							$(".alert").fadeOut(10000);
						</script>
					<?php } ?>
					<?php if($_GET["s"] == 2){?>
					<div class="alert alert-danger">
						<span class="glyphicon glyphicon-remove"></span><strong> Error! Please check all page inputs.</strong>
					</div>
					<?php } ?>
					<div id="noSession" class="alert alert-danger" style="display: none;">
						<span class="glyphicon glyphicon-remove"></span><strong> YOUR SESSION ENDED!! <br>(open in a new window and connect</strong>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<?php if($day_started == true){?>
			<form role="form" method="post" id="formId" class="col-lg-6">
				<div>
					<div class="form-inline" style="margin: 20px">
						<label for="InputName" style="    margin-right: 84px;">Timer</label>
						<div class="input-group">
							<input type="text" id="t" name="time" class="badge" value="0" style="width: 82px" title="you can set second and submit">
							<button type="button" id="btn" class="btn btn-success" style="margin: 0 20px">Start</button>
							<button type="button" class="btn btn-danger remove-timer-btn hidden">Remove Timer</button>
						</div>
					</div>
					<div class="form-inline" style="margin: 20px">
						<label for="InputName" style="    margin-right: 18px;">Time estimated</label>
						<div class="input-group">
							<input type="text" name="est_hours" class="form-control" placeholder="Hours" style="width: 83px;    border-top-left-radius: 7px;
    border-bottom-left-radius: 7px;" value="" title="">
						</div>
						<div class="input-group">
							<input type="text" name="est_min" class="form-control" placeholder="minutes" style="width: 83px;    border-top-right-radius: 7px;
    border-bottom-right-radius: 7px;" value="" title="">
						</div>
					</div>
					<hr />
					<div class="form-group">
						<label for="InputName">Project Name</label>
						<span id="pro_name"></span>
						<div class="input-group">
							<input type="text" value="<?php echo $_GET["project"];?>" class="form-control typeahead" autocomplete="off" name="project" id="InputName" placeholder="Choose Project" required>
							<span class="input-group-addon" id="loaderX"><span class="glyphicon glyphicon-asterisk"></span></span>
						</div>
					</div>
					<hr />
					<div class="form-group">
						<label>Choose type - סיווג</label>
						<ul class="input-group radios">

								<label for="sivug1">תמיכה</label>
								<input type="radio" name="sivug" id="sivug1" <?php echo (PodioUser::get()->id == 719578)?"checked":"" ?> value="1" />
								<label for="sivug2"> כללי</label>
								<input type="radio" id="sivug2" name="sivug" value="3">
								<label for="sivug3"> פיתוח</label>
								<input type="radio" id="sivug3" name="sivug" <?php echo (PodioUser::get()->id != 719578)?"checked":"" ?> value="4">
								<label for="sivug4"> הדרכה</label>
								<input type="radio" id="sivug4" name="sivug" value="5">
								<label for="sivug5"> QA / DBUG</label>
								<input type="radio" id="sivug5" name="sivug" value="7">

						</ul>
					</div>
					<hr />
					<div class="form-group">
						<label>Choose Joomla type - תכולה ג'ומלה</label>
						<div class="input-group radios">
							<label for="joomla1"> פיתוח דף בית</label><input type="radio" id="joomla1" name="joomla" value="1">
							<label for="joomla2"> פיתוח דף תוכן קטגוריה</label><input type="radio" id="joomla2" name="joomla" value="2">
							<label for="joomla3"> פיתוח דף תוכן</label><input type="radio" id="joomla3" name="joomla" value="3">
							<label for="joomla4"> פיתוח צור קשר</label><input type="radio" id="joomla4" name="joomla" value="4">
							<label for="joomla5">פיתוח קטלוג מוצרים</label><input type="radio" id="joomla5" name="joomla" value="5">
							<label for="joomla6"> פיתוח FAQ</label><input type="radio" id="joomla6" name="joomla" value="6">
							<label for="joomla7">פיתוח גלריית תמונות או סרטונים</label><input type="radio" id="joomla7" name="joomla" value="9">
							<label for="joomla8"> אחר</label><input type="radio" checked id="joomla8" name="joomla" value="8">
						</div>
					</div>
					<hr />
					<div class="form-group">
						<label>Choose Magento type - תכולה מג'נטו</label>
						<div class="input-group radios">
							<label for="magento1"> פיתוח דף בית</label><input type="radio" id="magento1" name="magento" value="1">
							<label for="magento2"> קטגוריה מוצרים</label><input type="radio" id="magento2" name="magento" value="2">
							<label for="magento3"> דף מוצר</label><input type="radio" id="magento3" name="magento" value="3">
							<label for="magento4"> הרשמה וכניסת משתמשים</label><input type="radio" id="magento4" name="magento" value="4">
							<label for="magento5">מודול מבצעים</label><input type="radio" id="magento5" name="magento" value="5">
							<label for="magento6"> עגלת קניות \ checkout</label><input type="radio" id="magento6" name="magento" value="6">
							<label for="magento7">איזור אישי</label><input type="radio" id="magento7" name="magento" value="7">
							<label for="magento8"> מודול משלוחים</label><input type="radio" id="magento8" name="magento" value="8">
							<label for="magento9"> צור קשר</label><input type="radio" id="magento9" name="magento" value="9">
							<label for="magento10"> חיבור לניוזלטר</label><input type="radio" id="magento10" name="magento" value="10">
						</div>
					</div>
					<hr />
					<div class="form-group">
						<label for="InputMessage">Enter description</label>
						<div class="input-group">
							<textarea name="description" id="InputMessage" class="form-control" rows="5" required></textarea>
							<span class="input-group-addon"><span class="glyphicon glyphicon-asterisk"></span></span>
						</div>
					</div>
					<input type="hidden" name="start" id="startNow" value="" class="">
					<input type="hidden" name="_id" id="_id" value="" class="">
					<input type="submit" name="submit" id="submit" value="Submit" class="btn btn-info pull-right">
				</div>
			</form>

			<div class="col-lg-6">
				<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
					<?php if($reported){?>
						<div class="panel panel-default">
							<div class="panel-heading" role="tab" id="headingOne">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOneA" aria-expanded="true" aria-controls="collapseOneA">משימות של היום</a> <?php
									$hours = floor($total / 3600);
									$mins = floor(($total % 3600) / 60);
									echo $hours.":".$mins; ?> |
									  תחילת יום:  <?php echo date("H:i:s", strtotime($day_started_at));?>
								</h4>
							</div>
							<div id="collapseOneA" class="panel-collapse collapse " role="tabpanel" aria-labelledby="headingOne">
								<div class="panel-body">
									<ul class="suppList">
										<?php foreach($reported as $reportedItem){
											$data = json_encode($reportedItem);
											$edit = "<a href='#' class='editItem' title='edit item' data-item='$data'>Edit</a> ";
											$hours = floor($reportedItem["time"] / 3600);
											$mins = floor(($reportedItem["time"] - ($hours*3600)) / 60);
//											$secs = floor($reportedItem["time"] % 60);
											echo "<li><a href='#".$reportedItem["item_id"]."' data-id='".$reportedItem["item_id"]."'>".$reportedItem["title"]."</a>  | <span>$hours:$mins</span> $edit</li>";
										}
										?>
									</ul>
								</div>
							</div>
						</div>
					<?php } ?>
					<?php if($suppArr){?>
						<div class="panel panel-default">
							<div class="panel-heading" role="tab" id="headingOne">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">מעטפת זהב</a>
								</h4>
							</div>
							<div id="collapseOne" class="panel-collapse collapse " role="tabpanel" aria-labelledby="headingOne">
								<div class="panel-body">
									<ul class="suppList">
										<?php foreach($suppArr as $zahav){
											echo "<li><a href='#".$zahav["id"]."' data-id='".$zahav["id"]."'>".$zahav["name"]."</a></li>";
										}
										?>
									</ul>
								</div>
							</div>
						</div>
					<?php } ?>
					<?php if($suppArr2){?>
					<div class="panel panel-default">
						<div class="panel-heading" role="tab" id="headingOne2">
							<h4 class="panel-title">
								<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne2" aria-expanded="true" aria-controls="collapseOne2">תחזוקה</a>
							</h4>
						</div>
						<div id="collapseOne2" class="panel-collapse collapse " role="tabpanel" aria-labelledby="headingOne2">
							<div class="panel-body">
								<ul class="suppList">
									<?php foreach($suppArr2 as $zahav){
										echo "<li><a href='#".$zahav["id"]."' data-id='".$zahav["id"]."'>".$zahav["name"]."</a></li>";
									}
									?>
								</ul>
							</div>
						</div>
					</div>
					<?php } ?>
					<?php if($suppArr3){?>
					<div class="panel panel-default">
						<div class="panel-heading" role="tab" id="headingOne3">
							<h4 class="panel-title">
								<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne3" aria-expanded="true" aria-controls="collapseOne3">אחריות</a>
							</h4>
						</div>
						<div id="collapseOne3" class="panel-collapse collapse " role="tabpanel" aria-labelledby="headingOne3">
							<div class="panel-body">
								<ul class="suppList">
									<?php foreach($suppArr3 as $zahav){
										echo "<li><a href='#".$zahav["id"]."' data-id='".$zahav["id"]."'>".$zahav["name"]."</a></li>";
									}
									?>
								</ul>
							</div>
						</div>
					</div>
					<?php } ?>
					<?php if($suppArr4){?>
						<div class="panel panel-default">
							<div class="panel-heading" role="tab" id="headingOne4">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne4" aria-expanded="true" aria-controls="collapseOne4">בנקי שעות</a>
								</h4>
							</div>
							<div id="collapseOne4" class="panel-collapse collapse " role="tabpanel" aria-labelledby="headingOne4">
								<div class="panel-body">
									<ul class="suppList">
										<?php foreach($suppArr4 as $zahav){
											echo "<li><a href='#".$zahav["id"]."' data-id='".$zahav["id"]."'>".$zahav["name"]."</a></li>";
										}
										?>
									</ul>
								</div>
							</div>
						</div>
					<?php } ?>
					<?php if($suppArr5){?>
						<div class="panel panel-default">
							<div class="panel-heading" role="tab" id="headingOne5">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne5" aria-expanded="true" aria-controls="collapseOne5">בעבודה</a>
								</h4>
							</div>
							<div id="collapseOne5" class="panel-collapse collapse " role="tabpanel" aria-labelledby="headingOne5">
								<div class="panel-body">
									<ul class="suppList">
										<?php foreach($suppArr5 as $zahav){
											echo "<li><a href='#".$zahav["id"]."' data-id='".$zahav["id"]."'>".$zahav["name"]."</a></li>";
										}
										?>
									</ul>
								</div>
							</div>
						</div>
					<?php } ?>
					<?php if($suppArr6){?>
						<div class="panel panel-default">
							<div class="panel-heading" role="tab" id="headingOne6">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne6" aria-expanded="true" aria-controls="collapseOne6">תקופת אחראיות</a>
								</h4>
							</div>
							<div id="collapseOne6" class="panel-collapse collapse " role="tabpanel" aria-labelledby="headingOne6">
								<div class="panel-body">
									<ul class="suppList">
										<?php foreach($suppArr6 as $zahav){
											echo "<li><a href='#".$zahav["id"]."' data-id='".$zahav["id"]."'>".$zahav["name"]."</a></li>";
										}
										?>
									</ul>
								</div>
							</div>
						</div>
					<?php } ?>
			</div>
				<a href="clock.php" target="_blank">דיווח שעות אישי</a>
		</div>
			<?php } ?>
	</div>
	<!-- Registration form - END -->

</div>

	<script>
		//var projects = <?php //echo json_encode($projArr);?>;
		window.onbeforeunload = function() {
			return "Are you sure?";
		};

		var startNow = "";
		(function(){
			$('#endday').on('click', function(){
				bootbox.confirm({
					message: "<ul style='direction:rtl'><li>האם כל הטאבים נסגרו ודווחו?</li><li>האם נשלח דוח סוף יום?</li><li>האם דווח ה-COMMIT של הגיט?</li></ul>",
					callback: function(result){
						if(result == true)
							$("#submitend").submit();
					}
				})
			});


			var $input = $('.typeahead');

			$(".panel-title a").click(function(e){
				e.preventDefault();
				var div = $(this).attr("href");
	//			$(div).slideToggle();
			});

			var value = '';

			$("a.editItem").click(function(e){
				e.preventDefault();
				startNow = true;
				var data = JSON.parse($(this).attr("data-item"));
				$("a.editItem").hide();
				$('.remove-timer-btn').removeClass('hidden');
				$('body').addClass('running');
				$('body').removeClass('pause');
				$("#t").timer({
					action: 'start',
					seconds:data.time
					//	format: '%s'
				});
				$("#btn").html("pause");
				$("input[name='s']").attr("disabled", "disabled");
				$("#t").addClass("badge-important");

				console.log(data);
				$("#pro_name").text(data.title);
				$("#_id").val(data._id);
				document.title = data.title;
				$input.val(data.item_id);

				value = data.syvvg[0].id;
				$("input[name=sivug][value=" + value + "]").attr("checked", true).button('refresh');
			});


			setInterval(function(){
				$.ajax({
					url: "form.php",
					data: {"userid": 1},
					success: function(data){
						console.log(data);
						if(data.res != true)
							$("#noSession").show();
						else
							$("#noSession").hide();
					}
				});
			},200000);



			$(".suppList li a").not(".editItem").click(function(){
				$input.val($(this).attr("data-id"));
				$("#pro_name").text($(this).text());
				document.title = $(this).text();
			});

			$( "form .radios" ).buttonset();

			var req = $.get('form.php', { query: "" }, function (data) {
				$("#loaderX").removeClass("loading");
	//			return process(data);
			});

			$input.typeahead({
				source: function (query, process) {
					$("#loaderX").addClass("loading");
					req.abort();
					req = $.get('form.php', { query: query }, function (data) {
						$("#loaderX").removeClass("loading");
						return process(data);
					});
					console.log(req);
					return req;
				},
				async: true,
				matcher: function(item) {
					var name = item.name;
					if(name.indexOf("לא פעיל") == -1)
					return true;
				},
				autoSelect: true,
				displayKey: 'id'
			});

			$input.change(function() {
				var current = $input.typeahead("getActive");
				if (current) {
					if (current.name == $input.val()) {
						$input.val(current.id);
						$("#pro_name").text(current.name);
						document.title = current.name;
					}
				}
			});


			//timer actions
			$("#btn").click(function(){
				var titleX = document.title;
				var orgTitleX = titleX.replace("*! ", "");
				if(!startNow) {
					startNow = Math.floor(Date.now() / 1000);
					$("#startNow").val(startNow);
				}

				switch($(this).html().toLowerCase())
				{
					case "start":
						$("a.editItem").hide();
						$('.remove-timer-btn').removeClass('hidden');
						$('body').addClass('running');
						$('body').removeClass('pause');
						var val = $(this).val();
						$("#t").timer({
							action: 'start',
							seconds:0
						//	format: '%s'
						});
						$(this).html("pause");
						$("input[name='s']").attr("disabled", "disabled");
						$("#t").addClass("badge-important");
						break;

					case "resume":
						$('body').removeClass('pause');
						$('body').addClass('running');
						if(titleX.indexOf("!*") > 0)
							document.title = orgTitleX;
						//you can specify action via string
						$("#t").timer('resume');
						$(this).html("pause")
						$("#t").addClass("badge-important");
						break;

					case "pause":
						$('body').addClass('pause');
						$('body').removeClass('running');
						if(titleX.indexOf("!*") > 0)
							document.title = orgTitleX;
						//you can specify action via object
						$("#t").timer('pause');
						$(this).html("resume")
						$("#t").removeClass("badge-important");
						break;
				}
			});


			$('#formId').on('submit', function() {
				if(!startNow) {
					alert("no time started!");
					return false;
				}
			});

			$('.remove-timer-btn').on('click', function() {
				if(!confirm("Are you sure ?? \n this will terminate the timer!!")) return;
				hasTimer = false;
				var titleX = document.title;
				var orgTitleX = titleX.replace("*! ", "");
				$('#t').timer('remove').val(0);
				$(this).addClass('hidden');
				$('body').removeClass('running');
				$('body').removeClass('pause');
				$('#btn').html("start");
				if(titleX.indexOf("!*") > 0)
					document.title = orgTitleX;
	//			$('.pause-timer-btn, .resume-timer-btn').addClass('hidden');
			});
		})();
	</script>
	<?php } ?>
</body>
</html>