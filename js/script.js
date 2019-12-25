$(function(){
	//================================
	// 攻撃時のエフェクト
	//================================
	$jsKnifeBtn = $('.js-knife-btn');
	$jsGunBtn = $('.js-gun-btn');
	$jsKnifeWound = $('.js-knife-wound');
	$jsBulletWound = $('.js-bullet-wound');
	// ナイフ攻撃
	if($jsKnifeBtn.hasClass('approach')){
		$jsKnifeBtn.on('click',function(){
			console.log('knife_clicked');
			$jsKnifeWound.show();
		})
	}
	// 銃攻撃
	if(!$jsGunBtn.hasClass('emptybullet')){
		$jsGunBtn.on('click',function(){
			console.log('gun_clicked');
			$jsBulletWound.show();
		})
	}


})