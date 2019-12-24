<?php
require('function.php');

/*========================================
  変数
========================================*/
// 主人公格納用の空の配列
$players[] = array();
// ゾンビ格納用の空の配列
$zombies[] = array();
/*========================================
  クラス
========================================*/
// 主人公選択クラス
class Character{
	const Julia = 0;
	const Charles = 1;
}
// 主人公・ゾンビ共通の抽象クラス(後でオーバーライド必須)
abstract class Creature{
	// プロパティ
	protected $name;
	protected $hp;
	protected $img;
	protected $minAttack;
	protected $maxAttack;
	protected $minVaccine;
	// コンストラクタ
	public function __construct($name,$hp,$img,$minAttack,$maxAttack,$minVaccine){
		$this->name = $name;
		$this->hp = $hp;
		$this->img = $img;
		$this->minAttack = $minAttack;
		$this->maxAttack = $maxAttack;
		$this->minVaccine = $minVaccine;
	}
	// セッターメソッド
	public function setName($str){
		$this->name = $str;
	}
	public function setHp($num){
		$this->hp = $num;
	}
	public function setImg($str){
		$this->img = $str;
	}
	public function setMinVaccine($num){
		$this->minVaccine = $num;
	}
	// ゲッターメソッド
	public function getName(){
		return $this->name;
	}
	public function getHp(){
		return $this->hp;
	}
	public function getImg(){
		return $this->img;
	}
	public function getMinVaccine(){
		return $this->minVaccine;
	}
	// 共通の抽象メソッド(後でオーバーライド必須)
	abstract public function reaction();
}
// 主人公クラス(後でインスタンス生成)
class Player extends Creature{
	// プロパティ
	protected $character;
	protected $bullet;
	protected $minGunAttack;
	protected $maxGunAttack;
	// コンストラクタ
	public function __construct($name,$hp,$img,$character,$bullet,$minAttack,$maxAttack,$minGunAttack,$maxGunAttack,$minvaccine){
		// 親のコンストラクタを継承
		parent::__construct($name,$hp,$img,$minAttack,$maxAttack,$minvaccine);
		$this->character = $character;
		$this->bullet = $bullet;
		$this->minGunAttack = $minGunAttack;
		$this->maxGunAttack = $maxGunAttack;
	}
	// セッターメソッド
	public function setCharacter($num){
		$this->character = $num;
	}
	public function setBullet($num){
		$this->bullet = $num;
	}	
	// ゲッターメソッド
	public function getCharacter(){
		return $this->character;
	}
	public function getBullet(){
		return $this->bullet;
	}
	// ナイフ攻撃メソッド
	public function knifeAttack($targetObj){
		$attackPoint = mt_rand($this->minAttack, $this->maxAttack);
		if(!mt_rand(0, 4)){		
			switch ($this->character) {
				case Character::Julia:
					History::set('Juliaのハイキック！');
					$attackPoint *= 2;
					break;
				case Character::Charles:
					History::set('Charlesのストレートパンチ！');
					$attackPoint *= 2.5;
					break;
			}
			$attackPoint = (int)$attackPoint;
		}else{
			History::set('ナイフで切り裂いた！');
		}
		$targetObj->setHp($targetObj->getHp()-$attackPoint);
		History::set($attackPoint.'のダメージ！');
	}
	// 銃攻撃メソッド
	public function gunAttack($targetObj){
		$attackPoint = mt_rand($this->minGunAttack, $this->maxGunAttack);
		History::set($this->getName().'は銃を撃った！');
		if(!mt_rand(0, 2)){
			switch ($this->character) {
				case Character::Julia:
					$attackPoint *= 2.5;
					break;
				case Character::Charles:
					$attackPoint *= 2;
					break;
			}			
			$attackPoint = (int)$attackPoint;
			History::set('ヘッドショット！');
		}
		$_SESSION['player']->setBullet($_SESSION['player']->getBullet()-1);
		$targetObj->setHp($targetObj->getHp()-$attackPoint);
		History::set($attackPoint.'のダメージ！');
	}
	// ワクチン入手メソッド
	public function gainVaccine(){
		$vaccinePoint = mt_rand($_SESSION['zombie']->getMinVaccine(), $_SESSION['zombie']->getMaxVaccine());
		if(!mt_rand(0, 3)){
			$vaccinePoint *= 1.5;
			$vaccinePoint = (int)$vaccinePoint;
		}
		$_SESSION['player']->setMinVaccine($_SESSION['player']->getMinVaccine()+$vaccinePoint);
		History::set('生き血から'.$vaccinePoint.'のワクチンを入手した！');
	}
	// ワクチン使用メソッド
	public function useVaccine(){
		$recoverPoint = $this->getMinVaccine();
		History::set($this->getName().'はワクチンを射った！');
		$this->setMinVaccine($_SESSION['player']->getMinVaccine()-$recoverPoint);
		$_SESSION['player']->setHp($_SESSION['player']->getHp()+$recoverPoint);
		History::set('HPが'.$recoverPoint.'回復した！');
	}
	// 逃げるメソッド
	public function escape(){
		History::set($this->getName().'は逃げ出した！');
		if(!mt_rand(0, 9)){
			History::set('しかし回り込まれてしまった！');
			$_SESSION['zombie']->attack($_SESSION['player']);
			$_SESSION['player']->reaction();
		}else{
			createZombie();
		}
	}
	// リアクションメソッド
	public function reaction(){
		switch ($this->character) {
			case Character::Julia:
				History::set('くっ！');
				break;
			case Character::Charles:
				History::set('ぐぁ！');
				break;
		}		
	}
}
// ゾンビクラス(後でインスタンス生成)
class Zombie extends Creature{
	// プロパティ
	protected $minAwayAttack;
	protected $maxAwayAttack;
	protected $voice;
	protected $maxVaccine;
	protected $virus;
	// コンストラクタ	
	public function __construct($name,$hp,$img,$minAttack,$maxAttack,$minAwayAttack,$maxAwayAttack,$voice,$minVaccine,$maxVaccine,$virus){
		// 親のコンストラクタを継承
		parent::__construct($name,$hp,$img,$minAttack,$maxAttack,$minVaccine);
		$this->minAwayAttack = $minAwayAttack;
		$this->maxAwayAttack = $maxAwayAttack;
		$this->voice = $voice;
		$this->maxVaccine = $maxVaccine;
		$this->virus = $virus;
	}
	// セッターメソッド
	public function setVoice($str){
		$this->voice = $str;
	}
	public function setMaxVaccine($num){
		$this->maxVaccine = $num;
	}
	public function setVirus($num){
		return $this->virus = $num;
	}
	// ゲッターメソッド
	public function getVoice(){
		return $this->voice;
	}
	public function getMaxVaccine(){
		return $this->maxVaccine;
	}
	public function getVirus(){
		return $this->virus;
	}
	// 近距離攻撃メソッド
	public function attack($targetObj){
		$attackPoint = mt_rand($this->minAttack, $this->maxAttack);
		History::set($this->getName().'の攻撃！');
		if(!mt_rand(0,4)){
			$attackPoint *= 1.5;
			$attackPoint = (int)$attackPoint;
			History::set('致命の一撃！');
		}
		$targetObj->setHp($targetObj->getHp()-$attackPoint);
		History::set($attackPoint.'のダメージ！');
	}
	// 遠距離攻撃メソッド
	public function awayAttack($targetObj){
		$attackPoint = mt_rand($this->minAwayAttack, $this->maxAwayAttack);
		History::set($this->getName().'は毒液を吐いた！');
		$targetObj->setHp($targetObj->getHp()-$attackPoint);
		History::set($attackPoint.'のダメージ！');
	}
	// リアクションメソッド
	public function reaction(){
		History::set($this->voice);
	}
}
// インターフェイス(メッセージ表示)
// 増殖しないのでstatic
interface HistoryInterface{
	public static function set($str);
	public static function clear();
}
class History implements HistoryInterface{
	public static function set($str){
		if(empty($_SESSION['history'])) $_SESSION['history'] = '';
		$_SESSION['history'] .= $str.'<br>';
	}
	public static function clear(){
		unset($_SESSION['history']);
	}
}
/*========================================
  インスタンス
========================================*/
// 主人公インスタンス
$players[0] = new Player('Julia',700,'img/julia_face.png',Character::Julia,50,20,30,70,90,0);
$players[1] = new Player('Charles',800,'img/charles_face.png',Character::Charles,50,30,55,55,65,0);
// ゾンビインスタンス
$zombies[0] = new Zombie('メンズゾンビ',280,'img/man_zombie.png',30,40,10,20,'うぅぅ！',10,20,50);
$zombies[1] = new Zombie('サダコゾンビ',300,'img/sadako.png',15,20,30,40,'おぉぉ！',10,15,25);
$zombies[2] = new Zombie('バイオレンスカラス',260,'img/raven.png',30,50,10,25,'キェー！',10,15,30);
$zombies[3] = new Zombie('ゾンビイヌ',200,'img/dog.png',35,55,15,30,'グワゥ！',15,25,45);

debug('SESSIONの中身(送信前)：'.print_r($_SESSION,true));
// POST送信されていた場合
if(!empty($_POST)  ){
	debug('POSTの中身：'.print_r($_POST,true));
	debug('SESSIONの中身(送信後)：'.print_r($_SESSION,true));
	$restart_flg = (!empty($_POST['restart_submit'])) ? true : false;
	$julia_flg = (!empty($_POST['julia_submit'])) ? true : false;
	$charles_flg = (!empty($_POST['charles_submit'])) ? true : false;
	History::clear();
	// スタートボタンが押された場合
	if($restart_flg){
		// セッションの中身を空にしてオープニング画面へ
		$_SESSION = array();
	}
	// 主人公ジュリアを選択
	if($julia_flg){
		init();
	}
	// 主人公チャールズを選択
	if($charles_flg){
		init();
	}
	if(!empty($_POST) && !empty($_SESSION['zombie']) ){
// 近づくボタンが押された場合
if( !empty($_POST['approach_submit']) ){
	$_SESSION['away'] = false;
}
// 離れるボタンが押された場合
if(!empty($_POST['away_submit'])){
	$_SESSION['away'] = true;
}
		$knife_flg = (!empty($_POST['knife_submit'])) ? true : false;
		$gun_flg = (!empty($_POST['gun_submit'])) ? true : false;
		$vaccine_flg = (!empty($_POST['vaccine_submit'])) ? true : false;
		$escape_flg = (!empty($_POST['escape_submit'])) ? true : false;		
		// 近くの場合
		if(empty($_SESSION['away'])){
			// ナイフ攻撃をした場合
			if($knife_flg){
				usleep(120000);
				$_SESSION['player']->knifeAttack($_SESSION['zombie']);
				$_SESSION['zombie']->reaction();
				// ワクチンを入手する
				$_SESSION['player']->gainVaccine();
				// ゾンビのHPが０になった場合
				if($_SESSION['zombie']->getHp() <= 0 ){
					killZombie();
				}else{
					// ゾンビから攻撃を受ける
					$_SESSION['zombie']->attack($_SESSION['player']);
					$_SESSION['player']->reaction();
				}		
			}
			// 銃攻撃をした場合
			if($gun_flg){
				if($_SESSION['player']->getBullet() <= 0 ){
					History::set('弾薬が無い！');
				}else{
					usleep(120000);
					$_SESSION['player']->gunAttack($_SESSION['zombie']);
					$_SESSION['zombie']->reaction();
					// ゾンビのHPが０になった場合
					if($_SESSION['zombie']->getHp() <= 0 ){
						killZombie();
					}else{
						// ゾンビから攻撃を受ける
						$_SESSION['zombie']->attack($_SESSION['player']);
						$_SESSION['player']->reaction();
					}		
				}
			}			
		// 遠くの場合
		}elseif(!empty($_SESSION['away'])){
			// ナイフ攻撃をした場合
			if($knife_flg){
				History::set('この距離では届かない！');
			}
			// 銃攻撃をした場合
			if($gun_flg){
				// 弾薬が０の場合
				if($_SESSION['player']->getBullet() <= 0 ){
					History::set('弾薬が無い！');
				}else{
					usleep(120000);
					$_SESSION['player']->gunAttack($_SESSION['zombie']);
					$_SESSION['zombie']->reaction();
					// ゾンビのHPが０になった場合
					if($_SESSION['zombie']->getHp() <= 0 ){
						killZombie();				
					}else{
						// ゾンビから攻撃を受ける
						$_SESSION['zombie']->awayAttack($_SESSION['player']);
						$_SESSION['player']->reaction();
					}		
				}
			}
		}
		// ワクチンボタンが押された場合
		if($vaccine_flg){
			$_SESSION['player']->useVaccine();
		}
		// 逃げるボタンを押した場合
		if($escape_flg){
			$_SESSION['player']->escape();			
		}
		// 主人公のHPが０になった場合
		if($_SESSION['player']->getHp() <= 0 ){
			gameOver();
		}
	}
// POST送信されていない場合
}elseif(empty($_POST)){
	$_SESSION = array();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<!-- レスポンシブ -->
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<!-- IE対応 -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge" >
	<!-- Twitter -->
	<meta property="og:url" content="https://ryonexta.com/portfolio/techhazard">
	<meta property="og:title" content="TECH HAZARD" >
	<meta property="og:description" content="ゾンビを倒してウィルスを集めるゲームです。ウェブカツオブジェクト指向部のアウトプット作品です。" >
	<meta property="og:image" content="https://ryonexta.com/wp-content/uploads/2019/12/tech_hazard_top.jpe" >
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:site" content="@ryonextStandard">
	<!-- 結果ツイート用 -->
	<script type="text/javascript" async src="//platform.twitter.com/widgets.js"></script>
	<!-- CSS -->
	<link rel="stylesheet" href="style.css">
	<!-- アイコン -->
	<link rel="icon" href="img/favicon.ico">
	<title>TECH HAZARD</title>
</head>
<body>
	<div class="main">
		<?php if(empty($_SESSION['player']) && empty($_SESSION['gameover']) ): ?>
			<div class="opening-container">
				<h1>TECH HAZARD</h1>
				<h2>MISSION：ゾンビのウィルスを入手せよ</h2>
				<div class="tips">
					<p>TIPS</p>
					<p>ガン攻撃は威力が高いが弾薬に限りがある。</p>
					<p>ナイフ攻撃で敵からワクチンを入手できる。ワクチンを使用するとHPが回復する。</p>
					<p>離れると被ダメージは減るが、ナイフ攻撃ができない。</p>
				</div>
				<p>▽主人公を選択してください▽</p>
				<form action="" method="post">
					<div class="select-player-area">
						<button type="submit" name="julia_submit" value="julia_submit">
							<div class="player-container">
								<div class="player-inner">
									<div class="player-img-area">
										<img src="img/julia_face.png" alt="">
										<p>Julia</p>
									</div>
									<div class="player-status-container">	
										<p>HP：700</p>
										<p>Knife：20-30</p>
										<p>Gun：70-90</p>
									</div>
								</div>
							</div>
						</button>
						<button type="submit" name="charles_submit" value="charles_submit">
							<div class="player-container">
								<div class="player-inner">
									<div class="player-img-area">
										<img src="img/charles_face.png" alt="">
										<p>Charles</p>
									</div>
									<div class="player-status-container">
										<p>HP：800</p>
										<p>Knife：30-55</p>
										<p>Gun：55-65</p>
									</div>
								</div>
							</div>
						</button>
					</div>
				</form>
			</div>
		<?php elseif(!empty($_SESSION) && empty($_SESSION['gameover']) ): ?>
		<div class="battle-container">
			<div class="title-area">
				<h1>TECH HAZARD</h1>
				<form action="" method="post">
					<button  type="submit" name="restart_submit" value="restart_submit">▷リスタート</button>
				</form>
			</div>
			<div class="battle-area <?php
						if($_SESSION['away']==false){
							echo 'approach';
						}elseif($_SESSION['away']==true){
							echo 'away';
						} ?>">
				<div class="enemy-area">
					<div class="enemy-status-area">
						<p><?php echo $_SESSION['zombie']->getName(); ?></p>
						<p>HP：<?php echo $_SESSION['zombie']->getHp(); ?></p>
					</div>
					<div class="enemy-img-area">
						<img src="<?php echo $_SESSION['zombie']->getImg(); ?>" alt="" class="zombie-img 
						<?php distanceClass(); ?>">
						<img src="img/knife_wound.png" alt="" width="200" class="js-knife-wound ">
						<img src="img/wound_bullet.png" alt="" width="90" class="js-bullet-wound ">
					</div>
				</div>
			</div>
			<div class="player-area">
				<div id="js-scroll-bottom" class="msg-wrapper">
					<?php if(!empty($_SESSION['history'])) echo $_SESSION['history']; ?>
				</div>
				<div class="player-wrapper">
					<div class="player-information-wrapper">
						<div class="player-information-inner">
							<div class="player-img-area">
								<img src="<?php echo $_SESSION['player']->getImg(); ?>" alt="">
							</div>
							<div class="player-status-container">
								<p><?php echo $_SESSION['player']->getName(); ?></p>
								<p>HP：<?php echo $_SESSION['player']->getHp(); ?></p>
								<p>VIRUS：<?php echo $_SESSION['virusCount']; ?></p>
							</div>
						</div>
					</div>
					<form action="" method="post">
						<div class="command-wrapper">
							<div class="command escape-command">
								<button type="submit" name="escape_submit" value="escape_submit">▷逃げる</button>
								<button type="submit" name="vaccine_submit" value="vaccine_submit">▷ワクチン<p>(<?php echo $_SESSION['player']->getMinVaccine(); ?>)</p></button>
								
							</div>
							<div class="command distance-command">
								<button type="submit" class="js-approach" name="approach_submit" value="approach_submit">▷近づく</button>
								<button type="submit" class="js-away" name="away_submit" value="away_submit">▷離れる</button>
							</div>
							<div class="command attack-command">
								<button type="submit" name="knife_submit" value="knife_submit" class="js-knife-btn <?php distanceClass() ?>">▷ナイフ</button>
								<button type="submit" name="gun_submit" value="gun_submit" class="js-gun-btn 
								<?php emptybulletClass(); ?>">▷ガン<p>(<?php echo $_SESSION['player']->getBullet(); ?>)</p></button>
								
							</div>
						</div>
					</form>
				</div>
				
			</div>
		</div>
	<?php elseif(!empty($_SESSION['gameover'])): ?>
		<div class="gameover-container">
			<div class="gameover-inner">
				<h1>YOU ARE DEAD</h1>
				<p>集めたウィルス：<?php echo $_SESSION['virusCount'];?></p>
				<p>称号：<?php echo getRank();?></p>
				<form action="" method="post">
					<a href="http://twitter.com/intent/tweet?url=https://ryonexta.com/portfolio/techhazard/&text=<?php echo $_SESSION['virusCount'];?>のウィルスを集め、「<?php echo getRank();?>」の称号を得ました！&related=ryonextStandard&hashtags=ウェブカツOP,TECH_HAZARD">▷Tweet</a>
					<button type="submit" name="restart_submit" value="restart_submit">▷continue</button>	
				</form>
			
			</div>
		</div>
	<?php endif; ?>
	</div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="js/script.js"></script>
	<script>
		$(function(){
			//================================
			// メッセージを下部に固定
			//================================
	        $('#js-scroll-bottom').animate({scrollTop: $('#js-scroll-bottom')[0].scrollHeight}, 'fast'); //#js-scroll-bottomをスクロールのトップにする。
	    });
	</script>
</body>
</html>
