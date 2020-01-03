<?php
/* Add categories and subcategories */

set_time_limit(7200);
include 'vce_conf.php';

$dir = "exams";
$dirs = array_diff(scandir($dir), array('..', '.'));
foreach ($dirs as $category)
{
	echo $category . " </br> ";
	
	// Check does caterory exists
	$sql = "SELECT * FROM `" . DB_PREFIX . "category_description` WHERE name = '" . $category ."'";
	$res = $db->query($sql);
	if ($res === false) {
		trigger_error('Wrong SQL:' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
	}
	else {
		$found = $res->num_rows;
		//echo $found . " </br> ";
		if ($found == 0) {
			create_category($category, $db);
		}
	}
	
	//* go inside
	// Create Subcategories
	$sub_dirs = array_diff(scandir("exams/".$category), array('..', '.'));
	//* get subcategory names
	foreach ($sub_dirs as $sub_category)
	{
		$sql = "SELECT * FROM `" . DB_PREFIX . "category_description` WHERE name = '" . $sub_category ."'";
		$res = $db->query($sql);
		if ($res === false) {
			trigger_error('Wrong SQL:' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
		}
		else {
			$found = $res->num_rows;
			echo $found . " </br> ";
			if ($found == 0) {
				$sql = "SELECT category_id FROM `" . DB_PREFIX . "category_description` WHERE name = '" . $category ."'";
				$res = $db->query($sql);
				while ($row = $res->fetch_assoc()) {
					$category_id = $row['category_id'];
				}
				create_subcategory($sub_category, $category_id, $category, $db);
			}
		}
	}
}
add_products($db);

//* create 1-st level category
function create_category($category, $db) {
	//echo "Create <br />";
	
	// category table
	$sql = "INSERT INTO " . DB_PREFIX . 
			"category SET parent_id = '0',
			`top` = '1',
			`column` = '1',
			sort_order = '0',
			status = '1',
			date_modified = NOW(),
			date_added = NOW()";
	if ($db->query($sql) === false)
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
	} else {
		$category_id = $db->insert_id;
	}
	
	// category_description table
	$sql = "INSERT INTO " . DB_PREFIX . "category_description SET
		category_id = '" . (int)$category_id . "', 
		language_id = '" . LANGUAGE_ID . "', 
		name = '" . strtoupper($category) . "', 
		description = '" . $category . "', 
		meta_title = '" . $category . " VCE exams', 
		meta_description = '" . $category . " VCE exams', 
		meta_keyword = '" . $category . " VCE exams'";
	if ($db->query($sql) === false)
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
	} 
	
	// Create category patch 
	$level = 0;
	
	$sql ="INSERT INTO `" . DB_PREFIX . "category_path` SET
				`category_id` = '" . (int)$category_id . "', 
				`path_id` = '" . (int)$category_id . "', 
				`level` = '" . (int)$level . "'";
			
	if ($db->query($sql) === false)
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
	}
	
	// Category to store
	$sql = "INSERT INTO " . DB_PREFIX . "category_to_store SET
		category_id = '" . (int)$category_id . "', 
		store_id = '" . (int)STORE_ID . "'";
	
	if ($db->query($sql) === false)
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
	}	
	
	// URL alias
	$sql = "INSERT INTO " . DB_PREFIX . "url_alias SET
		query = 'category_id=" . (int)$category_id . "', 
		keyword = '" . $category. "'";
	if ($db->query($sql) === false)
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
	}

	echo "Category added " . $category . "</br>";
};	
	
//* create subcategories	
function create_subcategory($sub_category, $category_id, $category, $db) {
	// category table
	$sql = "INSERT INTO " . DB_PREFIX . 
			"category SET 
			`parent_id` = '" . $category_id . "',
			`top` = '0',
			`column` = '0',
			`sort_order` = '0',
			`status` = '1',
			`date_modified` = NOW(),
			`date_added` = NOW()";
	if ($db->query($sql) === false)
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
	} else {
		$sub_category_id = $db->insert_id;
	}
	
	// category_description table
	$sql = "INSERT INTO " . DB_PREFIX . "category_description SET
		category_id = '" . (int)$sub_category_id . "', 
		language_id = '" . LANGUAGE_ID . "', 
		name = '" . strtoupper($sub_category) . "', 
		description = '" . $sub_category . "', 
		meta_title = '" . $sub_category . " VCE exams', 
		meta_description = '" . $sub_category . " VCE exams', 
		meta_keyword = '" . $sub_category . " VCE exams'";
	if ($db->query($sql) === false)
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
	}
	
	// Create category patch, level 0
	$sql ="INSERT INTO `" . DB_PREFIX . "category_path` SET
				`category_id` = '" . (int)$sub_category_id . "', 
				`path_id` = '" . (int)$category_id . "', 
				`level` = '0'";
	if ($db->query($sql) === false)
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
	}
	
	// Create category patch, level 1
	$sql ="INSERT INTO `" . DB_PREFIX . "category_path` SET
				`category_id` = '" . (int)$sub_category_id . "', 
				`path_id` = '" . (int)$sub_category_id . "', 
				`level` = '1'";
	if ($db->query($sql) === false)
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
	}
	
	// Category to store
	$sql = "INSERT INTO " . DB_PREFIX . "category_to_store SET
		category_id = '" . (int)$sub_category_id . "', 
		store_id = '" . (int)STORE_ID . "'";
	
	if ($db->query($sql) === false)
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
	}	
	
	// URL alias
	$sql = "INSERT INTO " . DB_PREFIX . "url_alias SET
		query = 'category_id=" . (int)$sub_category_id . "', 
		keyword = '" . $category . "-" . $sub_category. "'";
	if ($db->query($sql) === false)
	{
		trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
	}
	
}

function add_products($db) {
	$dir = "vce";
	$files = array_diff(scandir($dir), array('..', '.'));
	foreach ($files as $file) {
		// move and rename file
		$new_file = $file . uniqid() . ".vce";
		rename("vce/" . $file, "../system/storage/download/" . $new_file);
		echo "product found " . $file . "</br>";
		//* get category
		$cats = explode(".", $file); 
		//* get subcategory
		$file_cat = $cats[0];
		$file_sub_cat = $cats[2];
		$sql = "SELECT cd.`category_id` FROM `" . DB_PREFIX . "category_description` cd
				INNER JOIN `" . DB_PREFIX . "category` c ON c.`category_id` = cd.`category_id`	
				WHERE cd.`name` = '" . $file_sub_cat ."' 
				and c.`parent_id` = (
					SELECT cd.`category_id` 
					FROM `" . DB_PREFIX . "category_description` cd
					INNER JOIN `" . DB_PREFIX . "category` c
					ON c.`category_id` = cd.`category_id`	
					WHERE cd.`name` = '" . $file_cat ."' and c.`parent_id` = 0)";
		$res = $db->query($sql);
		while ($row = $res->fetch_assoc()) {
			$sub_category_id = $row['category_id'];
		}
		if (!$sub_category_id) {
			$sql = "SELECT `category_id` FROM `" . DB_PREFIX . "category_description` 
				WHERE `name` = '" . $file_cat ."'";
			$res = $db->query($sql);
			while ($row = $res->fetch_assoc()) {
				$sub_category_id = $row['category_id'];
			}	
		}
		$file_date = str_replace("v", "", $cats[3]);
		$file_questions = str_replace("q", "", $cats[6]);
		//* insert data into db
		
		// product table
		$sql = "INSERT INTO " . DB_PREFIX . "product SET 
			model = '" . $db->real_escape_string($file) . "', 
			image = 'catalog/file_logo.png',
			quantity = '100', 
			minimum = '1', 
			subtract = '0', 
			stock_status_id = '6', 
			date_available = NOW(),
			manufacturer_id = '0',
			shipping = '0',
			price = '1.0000', 
			points = '0', 
			weight = '0',
			weight_class_id = '1',
			length = '0',
			width = '0',
			height = '0',
			length_class_id = '1',
			status = '1',
			tax_class_id = '10',
			sort_order = '1',
			date_added = NOW(),
			date_modified = NOW()";
		if ($db->query($sql) === false) {
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
		} else {
			$product_id = $db->insert_id;
		}
		
		// product_description
		$sql = "INSERT INTO " . DB_PREFIX . "product_description SET
			product_id = '" . (int)$product_id . "',
			language_id = '" . LANGUAGE_ID . "',
			name = '" . $db->real_escape_string($file) . "',
			description = '', 
			tag = '" . $file_cat . "', 
			meta_title = '" . $file_cat . "', 
			meta_description = '" . $file_cat . " " . $file_sub_cat . "', 
			meta_keyword = '" . $file_cat . " " . $file_sub_cat . "'";
		if ($db->query($sql) === false) {
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
		}
		
		// product_to_store
		$sql = "INSERT INTO " . DB_PREFIX . "product_to_store SET 
			product_id = '" . (int)$product_id . "', 
			store_id = '0'";
		if ($db->query($sql) === false) {
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
		}
		
		// product_attribute - date
		$sql = "INSERT INTO " . DB_PREFIX . "product_attribute SET 
			product_id = '" . (int)$product_id . "', 
			attribute_id = '12',
			language_id = '" . LANGUAGE_ID . "',
			text = '" . $db->real_escape_string($file_date) . "'";
		if ($db->query($sql) === false) {
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
		}
		
		// product_attribute - questions
		$sql = "INSERT INTO " . DB_PREFIX . "product_attribute SET 
			product_id = '" . (int)$product_id . "', 
			attribute_id = '13',
			language_id = '" . LANGUAGE_ID . "',
			text = '" . $db->real_escape_string($file_questions) . "'";
		if ($db->query($sql) === false) {
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
		}
		
		// download
		$sql = "INSERT INTO " . DB_PREFIX . "download SET
			filename = '" . $new_file . "', 
			mask = '" . $file . "', date_added = NOW()";
		if ($db->query($sql) === false) {
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
		} else {
			$download_id = $db->insert_id;
		}
		
		// download_description
		$sql = "INSERT INTO " . DB_PREFIX . "download_description SET
			download_id = '" . (int)$download_id . "', 
			language_id = '" . LANGUAGE_ID . "', 
			name = '" . $file_cat . "_" . $file_sub_cat . "_" . $file_date . "_q" . $file_questions . "'";
		if ($db->query($sql) === false) {
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
		}

		// product_to_download
		$sql = "INSERT INTO " . DB_PREFIX . "product_to_download SET 
			product_id = '" . (int)$product_id . "', 
			download_id = '" . (int)$download_id . "'";
		if ($db->query($sql) === false) {
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
		}
		
		// URL alias
		$sql = "INSERT INTO " . DB_PREFIX . "url_alias SET
			query = 'product_id=" . (int)$product_id . "', 
			keyword = '" . $db->real_escape_string($file). "'";
		if ($db->query($sql) === false)
		{
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
		}
		
		// product_to_category
		$sql = "INSERT INTO " . DB_PREFIX . "product_to_category SET 
			product_id = '" . (int)$product_id . "', 
			category_id = '" . (int)$sub_category_id . "'";
		if ($db->query($sql) === false) {
			trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $db->error, E_USER_ERROR);
		}
		echo "File added " . $file . " - " . $new_file . "</br>";
		
	}
	
}
?>	
