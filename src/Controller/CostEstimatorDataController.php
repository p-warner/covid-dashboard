<?php

namespace Drupal\cost_estimator\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;

use Drupal\cost_estimator\Form\CostEstimatorForm;

/**
 * This class contains utility functions used throughout 
 * the cost_estimator module. It is also responsible for the auditing page.
 */
class CostEstimatorDataController extends ControllerBase{
	
	/**
	 * Provides an array of all costs for auditing.
	 * 
	 * @return Array an array of data for the twig to render.
	 */
	public function audit() {		
		//get all majors
		$data['majors'] = $this->getMajors();

		//get all costs for call majors
		$data['costs'] = [];
		foreach($data['majors'] as $cluster){
			foreach($cluster as $major){
				$data['costs'][$major->pgm] = $this->getCost($major->pgm);
			}
		}

		return [
      '#theme' => 'cost_estimator_audit',
      '#data' => $data,
    ];
		
	}

	/**
   * Gives an array that represents the number of credits per semester, lab hours per semester, tools and 
	 * uniform costs, major fee, book costs per semester.
	 * 
	 * @param String A major code. E.g. BGD, BSI, DD, etc.
	 * @return Array A keyed array that represents a lot of different datas.
   */
  public static function getCost($major_code) {
    if($major_code === 'UD'){
      $major_code = 'GS';
    }
		
		$sql = "SELECT * FROM cost_data WHERE `code` = '".$major_code."'";
		$database = \Drupal\Core\Database\Database::getConnection('default','pct_data');
		$query = $database->query($sql);
		$result = $query->fetchAll();

		foreach ($result as $record) {  
			$c1 = $record->credits_1;
			$c2 = $record->credits_2;
			$cs = $record->credits_s;
			$l1 = $record->lab_1;
			$l2 = $record->lab_2;
			$ls = $record->lab_s;
			$t = $record->tool_1 + $record->tool_2 + $record->tool_s + $record->uniform;
			$mf = $record->majorfee;
		}
		//Fake major here.
		if(strtoupper($major_code) == 'XBF'){$c1 = 15;$c2 = 14;$cs = 0;$l1 = 4.5;$l2 = 3;$ls = 0;$t = 0;$mf = 0;}
		
		$books_sem = $cs>0?[1,2,3]:[1,2];
		$total_fb =[1=>0,2=>0,3=>0];
		$books_added = [];
		foreach($books_sem as $semester){
			$sem = $semester == 3 ? 2.5 : $semester;
			$sql = "SELECT c.`code`,c.`course`,c.`semester`,s.`new` as `price`,s.section, s.isbn FROM (cost_data_course as c JOIN F19books AS s ON c.course = replace(s.course,' ','') ) where c.code = '".$major_code."' AND c.semester = ".$sem." ORDER BY c.course, s.section";
			$database = \Drupal\Core\Database\Database::getConnection('default','pct_data');
			$query = $database->query($sql);
			$result = $query->fetchAll();

			/**
			 * Loop through each class and make and check if the books isbn already exists 
			 * in the books_added array. If it's not in there, the price will be added 
			 * to the semesters total.
			 */
			foreach ($result as $record) {
				if(!in_array($record->isbn, $books_added)){
					$total_fb[$semester] += $record->price;
					array_push($books_added, $record->isbn);
				}
			}
		}

		$combo_array = [];
		$combo_array['n'] = $major_code;
		$combo_array['c1'] = (int)$c1;
		$combo_array['c2'] = (int)$c2;
		$combo_array['cs'] = (int)$cs;
		$combo_array['l1'] = (float)$l1;
		$combo_array['l2'] = (float)$l2;
		$combo_array['ls'] = (float)$ls;
		$combo_array['tu'] = (int)$t;
		$combo_array['mf'] = (int)$mf;
		$combo_array['b1'] = (int)$total_fb[1];
		$combo_array['b2'] = (int)$total_fb[2];
		$combo_array['bs'] = (int)$total_fb[3];
		//$combo_array[3] =$r_arr;
		//return new JsonResponse($results);
		
    return $combo_array;
  }

	/**
	 * Returns array of majors keyed by major code with basic major info.
	 * 
	 * @return Array major code keyed array with basic major attributes.
	 * 
	 * [
	 * 	'BGD' => [
	 * 		'pgm' => 'BGD',
	 * 		'pgmname' => 'Graphic Design',
	 * 		'degree' => ', B.S.',
	 * 		'school' => 3,
	 * 	],
	 * 	...
	 * ];
	 */
	public static function getMajors() {
		$sql = "SELECT cd.* ,i.pgmname, i.pgmClusterDesc ,i.degree, i.`true distance` as `dist`, i.sort, i.school  FROM (cost_data AS cd JOIN ISERIES_Degree_List AS i ON cd.code=i.pgm) ORDER BY i.pgmClusterDesc ,i.sort, i.pgmname, i.dist DESC;";
		$database = \Drupal\Core\Database\Database::getConnection('default','pct_data');
		$query = $database->query($sql);
		$result = $query->fetchAll();
		
		$o_arr = [];
		foreach ($result as $record) {
			$dist = $record->dist == 'Y' ? ' | online' : '';
      
      $deg= '';
			$deg = $record->degree=="AA"?', A.A.':$deg ;
			$deg = $record->degree=="AAA"?', A.A.A.':$deg ;		
			$deg = $record->degree=="AAS"?', A.A.S.':$deg ;
			$deg = $record->degree=="BS"?', B.S.':$deg ;
			$deg = $record->degree=="BSMS"?', B.S./M.S.':$deg ;
      $deg = $record->degree=="CERTIF"?', Certificate':$deg ;
      $deg = $record->degree=="MS"?', M.S.N.':$deg ;
      
      $object = [
        'pgm' => $record->code,
        'pgmname' => $record->pgmname . $dist,
        'degree' => $deg,
        'school' => $record->school,
      ];
      
			$o_arr[$record->pgmClusterDesc][$record->code] = (object) $object;
		}
    
    //Remove old majors
    $sql = "SELECT pgm, pgmClusterDesc FROM cost_data_program_exceptions WHERE new_old = 'old';";
		$database = \Drupal\Core\Database\Database::getConnection('default','pct_data');
		$query = $database->query($sql);
		$result = $query->fetchAll();

		foreach ($result as $record) {
			unset($o_arr[$record->pgmClusterDesc][$record->pgm]);
		}

		return $o_arr;
	}

/**
 * DEPRECATED
 */
public function cost_list_new($major_code = NULL){
	if($major_code === 'UD'){
		$major_code = 'GS';
	}

	$sql = "SELECT * FROM cost_data WHERE `code` = '".$major_code."'";
	$database = \Drupal\Core\Database\Database::getConnection('default','pct_data');
	$query = $database->query($sql);
	$result = $query->fetchAll();

	foreach ($result as $record) {
					$c1 = $record->credits_1;
					$c2 = $record->credits_2;
					$cs = $record->credits_s;
					$l1 = $record->lab_1;
					$l2 = $record->lab_2;
					$ls = $record->lab_s;
					$t = $record->tool_1 + $record->tool_2 + $record->tool_s + $record->uniform;
					$mf = $record->majorfee;
	}
	//Fake major here.
	if(strtoupper($major_code) == 'XBF'){ $c1 = 15;$c2 = 14;$cs = 0;$l1 = 4.5;$l2 = 3;$ls = 0;$t = 0;$mf = 0;}

	$books_sem = $cs>0?[1,2,3]:[1,2];//,3];
	$total_fb =[1=>0,2=>0,3=>0];
	foreach($books_sem as $semester){
		$sem = $semester==3?2.5:$semester;

		$sql = "SELECT c.`code`,c.`course`,c.`semester`,s.`new` as `price`,s.section, s.isbn FROM (cost_data_course as c JOIN F19books AS s ON c.course = replace(s.course,' ','') ) where c.code = '".$major_code."' AND c.semester = ".$sem." ORDER BY c.course, s.section";
		$query = $database->query($sql);
		$result = $query->fetchAll();

		foreach ($result as $record) {
			$total_fb[$semester] += $record->price;
			if(($cur_course != $record->course)|| ($cur_course == $record->course && $cur_section == $record->section)){
				$cur_course = $record->course;
				$cur_section = $record->section;
			} else {
				$total_fb[$semester] -= $record->price;
			}
		}
	}

	$combo_array = [];
	$combo_array['major_code'] = $major_code;
	$combo_array['credits_1'] = (int)$c1;
	$combo_array['credits_2'] = (int)$c2;
	$combo_array['credits_3'] = (int)$cs;
	$combo_array['lab_1'] = (float)$l1;
	$combo_array['lab_2'] = (float)$l2;
	$combo_array['lab_3'] = (float)$ls;
	$combo_array['tools_and_uniform'] = (int)$t;
	$combo_array['major_fee'] = (int)$mf;
	$combo_array['books_1'] = (int)$total_fb[1];
	$combo_array['books_2'] = (int)$total_fb[2];
	$combo_array['books_3'] = (int)$total_fb[3];

	$TUITION_RATE_INSTATE = 587;
	$TUITION_RATE_OUTSTATE = 839;
	$LAB_FEE = 45;
	$INTERNATIONAL_FEE = 500;
	$STUDENT_ENROLLMENT_FEE = 140;

	$total = 0;

	$request = \Drupal::request();
	$parameter_bag = $request->query;
	$querystring = $parameter_bag->all();

	//Tuition
	if($querystring['residency'] && $querystring['residency'] == "outstate"){
					$total += ($combo_array['credits_1'] + $combo_array['credits_2'] + $combo_array['credits_3']) * $TUITION_RATE_OUTSTATE;
	}else{
					$total += ($combo_array['credits_1'] + $combo_array['credits_2'] + $combo_array['credits_3']) * $TUITION_RATE_INSTATE;
	}

	//International fee
	if($querystring['international'] && $querystring['international'] == "true"){
					$total += $INTERNATIONAL_FEE;
	}else{
					$total += $STUDENT_ENROLLMENT_FEE;
	}

	//Lab fee
	$total += ceil(($combo_array['lab_1'] + $combo_array['lab_2'] + $combo_array['lab_3']) * $LAB_FEE);

	//Books
	$total += $combo_array['books_1'] + $combo_array['books_2'] + $combo_array['books_3'];

	//Major fee
	$total += $combo_array['major_fee'];

	//Tools and uniforms
	$total += $combo_array['tools_and_uniform'];

	//Housing
	if($querystring['housing']){
					$total += (int)$querystring['housing'];
	}

	//Dining
	if($querystring['dining']){
					$total += (int)$querystring['dining'];
	}

	$combo_array['total'] = (int)$total;

	$response['total'] = $combo_array['total'];

	return $response['total'];
	//return new JsonResponse( $response );
	}
}