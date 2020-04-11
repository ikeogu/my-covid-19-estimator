<?php
include_once 'impact.php';
include_once 'severeImpact.php';

$data = [
  'region' => [
    'name'=> 'África',
    'avgAge' =>19.7,
    'avgDailyIncomeInUSD' => 5,
    'avgDailyIncomePopulation'=> 0.71, 
  ],
  'periodType' => "months",
  'reportedCases' => 674,
  'timeToElapse' => 58,
  'population' => 66622705,
  'totalHospitalBeds' => 1380614
];
function covid19ImpactEstimator($data)
{

  $avliableBeds = (0.35 * $data['totalHospitalBeds']);
  $day = period($data['periodType'],$data['timeToElapse']);
  $factor = round($day/3);

  $income = $data['region']['avgDailyIncomeInUSD'];
  $population = $data['region']['avgDailyIncomePopulation'];
  

  $impact = $data;
  $severeImpact = $data;
  $reportedCases = $data['reportedCases'];

   $impact = new Impact();
   $severeImpact = new SevereImpact();

  //  reportedCases
   $impact->currentlyInfected = $reportedCases * 10;
   $severeImpact->currentlyInfected = $reportedCases * 50;
  //  infectionsByRequestedTime
  $impact->infectionsByRequestedTime  = $impact->currentlyInfected * (2 * $factor);
  $severeImpact->infectionsByRequestedTime = $severeImpact->currentlyInfected * (2 * $factor);
  // severeCasesByRequestedTime
  $impact->severeCasesByRequestedTime = 0.15 * $impact->infectionsByRequestedTime;
  $severeImpact->severeCasesByRequestedTime = 0.15 * $severeImpact->infectionsByRequestedTime;
  // bed availability in the hospitals for positive patient
  $impact->hospitalBedsByRequestedTime = ceil($avliableBeds - $impact->severeCasesByRequestedTime)+1;
  $severeImpact->hospitalBedsByRequestedTime =ceil($avliableBeds - $severeImpact->severeCasesByRequestedTime) +1;

  // casesForICUByRequestedTime
  $impact->casesForICUByRequestedTime = 0.05 * $impact->infectionsByRequestedTime;
  $severeImpact->casesForICUByRequestedTime = 0.05 * $severeImpact->infectionsByRequestedTime;
  // casesForVentilatorsByRequestedTime
  $impact->casesForVentilatorsByRequestedTime = 0.02 * $impact->infectionsByRequestedTime;
  $severeImpact->casesForVentilatorsByRequestedTime = 0.02 * $severeImpact->infectionsByRequestedTime;

  $impact->dollarsInFlight = ($impact->infectionsByRequestedTime * $income )* $population * $day;
  $severeImpact->dollarsInFlight = ($severeImpact->infectionsByRequestedTime * $income ) * $population * $day;

  
 
  $arr = [

   'data' => $data,
    'impact' => json_decode(json_encode($impact), true),
    'severeImpact' =>  json_decode(json_encode($severeImpact), true)
  ];
  return $arr;
}

function period($periodType,$timeToElapse){
  if($periodType === "weeks"){
    $timeToElapse *= 7;
  }elseif($periodType === "months"){
    $timeToElapse *= 30;
  }elseif($periodType === "days"){
    $timeToElapse ;
  }
   return $timeToElapse;
}


covid19ImpactEstimator($data);