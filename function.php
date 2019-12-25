<?php
/*========================================
  ログ
========================================*/
ini_set('log_errors', 'on'); //ログを取るか
ini_set('error_log', 'php.log'); //ログの出力ファイル
ini_set('display_errors', 'on'); //画面にエラーを表示するか
error_reporting(E_ALL); //E_STRICT以外のエラーを報告する

// セッションを開始する
session_start();
/*========================================
  デバッグ
========================================*/
$debug_flg = true;
function debug($str){
	global $debug_flg;
	if(!empty($debug_flg)){
		error_log('デバッグ：'.$str);
	}
}
/*========================================
  関数
========================================*/
// スタート
function init(){
	$_SESSION = array();
	History::clear();
	History::set('MISSION START');
	// 集めたウィルス
  	$_SESSION['virusCount'] = 0;
	// 主人公生成
	createPlayer();
	// ゾンビ生成
	createZombie();
}
// ゲームオーバー
function gameOver(){
	$_SESSION['gameover'] = true;
}
// 主人公生成
function createPlayer(){
	global $players;
	global $julia_flg;
	global $charles_flg;
	if($julia_flg){
		$_SESSION['player'] = $players[0];
	}elseif($charles_flg){
		$_SESSION['player'] = $players[1];
	}
}
// ゾンビ生成
function createZombie(){
	global $zombies;
	$_SESSION['zombie'] = $zombies[random_int(0, 3)];
	$_SESSION['away'] = true;
	History::set($_SESSION['zombie']->getName().'が現れた！');
}
// ゾンビを倒した時
function killZombie(){	
	History::set($_SESSION['zombie']->getName().'を倒した！');
	// ウィルスを入手
	$_SESSION['virusCount'] += $_SESSION['zombie']->getVirus();
	// 新たなゾンビを生成
	createZombie();	
}
// 距離によるクラス付与
function distanceClass(){
	if($_SESSION['away']==false){
		echo 'approach';		
	}elseif($_SESSION['away']==true){
		echo 'away';
	}
}
// 弾薬が０の時のクラス付与
function emptybulletClass(){
	if($_SESSION['player']->getBullet()<=0){
		echo 'emptybullet';
	}
}
// ゲームオーバー時の称号
function getRank(){
	$virusPoint = $_SESSION['virusCount'];
	if(0 <= $virusPoint && $virusPoint <= 99 ){
		echo '足手まとい';
	}
	if(100 <= $virusPoint && $virusPoint <= 249 ){
		echo '街のゴロツキ';
	}
	if(250 <= $virusPoint && $virusPoint <= 399 ){
		echo '用心棒';
	}
	if(400 <= $virusPoint && $virusPoint <= 549 ){
		echo 'モブ警官';
	}
	if(550 <= $virusPoint && $virusPoint <= 699 ){
		echo '新任警官';
	}
	if(700 <= $virusPoint && $virusPoint <= 849 ){
		echo 'ベテラン警官';
	}
	if(850 <= $virusPoint && $virusPoint <= 999 ){
		echo '特殊部隊U.B.C.S.の隊員';
	}
	if(1000 <= $virusPoint && $virusPoint ){
		echo '特殊部隊S.T.A.R.S.の隊員';
	}
}


?>