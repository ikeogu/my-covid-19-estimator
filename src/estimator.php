<?php
include_once 'impact.php';
include_once 'severeImpact.php';

$da = [
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
  $severImpact = $data;
  $reportedCases = $data['reportedCases'];

   $impact = new Impact();
   $severImpact = new SevereImpact();

  //  reportedCases
   $impact->currentlyInfected = $reportedCases * 10;
   $severImpact->currentlyInfected = $reportedCases * 50;
  //  infectionsByRequestedTime
  $impact->infectionsByRequestedTime  = $impact->currentlyInfected * (2 ** $factor);
  $severImpact->infectionsByRequestedTime = $severImpact->currentlyInfected * (2 ** $factor);
  // severeCasesByRequestedTime
  $impact->severeCasesByRequestedTime = 0.15 * $impact->infectionsByRequestedTime;
  $severImpact->severeCasesByRequestedTime = 0.15 * $severImpact->infectionsByRequestedTime;
  // bed availability in the hospitals for positive patient
  $impact->hospitalBedsByRequestedTime = round($avliableBeds - $impact->severeCasesByRequestedTime);
  $severImpact->hospitalBedsByRequestedTime =round($avliableBeds - $severImpact->severeCasesByRequestedTime);

  // casesForICUByRequestedTime
  $impact->casesForICUByRequestedTime = 0.05 * $impact->infectionsByRequestedTime;
  $severImpact->casesForICUByRequestedTime = 0.05 * $severImpact->infectionsByRequestedTime;
  // casesForVentilatorsByRequestedTime
  $impact->casesForVentilatorsByRequestedTime = 0.02 * $impact->infectionsByRequestedTime;
  $severImpact->casesForVentilatorsByRequestedTime = 0.02 * $severImpact->infectionsByRequestedTime;

  $impact->dollarsInFlight = ($impact->infectionsByRequestedTime * $income )* $population * $day;
  $severImpact->dollarsInFlight = ($severImpact->infectionsByRequestedTime * $income ) * $population * $day;


  return [
   'data'=> $data,
    'impact'=>$impact,
    'severImpact'=> $severImpact
];
}

function period($periodType,$timeToElapse){
  if($periodType === "weeks"){
    $timeToElapse *= 7;
  }elseif($periodType === "months"){
    $timeToElapse *= 30;
  }
   return $timeToElapse;
}


covid19ImpactEstimator($da);