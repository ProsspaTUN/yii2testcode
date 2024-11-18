<?php

$this->title = 'Профиль';
$this->params['breadcrumbs'][] = [
    'label' => 'Профиль',
];

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\file\FileInput;

$this->registerJs(<<<JS
     $(document).ready(function(){
        $(document).on('change', '[data-toggle-visibility]input[type="radio"]', toggelBlockVisibility);
        $(document).on('click', '[data-send-info]', sendInfo);
        $(document).on('change', '[data-send-info-checkbox]', sendBox);
        
        })
        
        function toggelBlockVisibility(e){
            let trg = e.currentTarget,
                value = trg.dataset.value ? trg.dataset.value : trg.value,
                blockList = Array.from(trg.closest('[data-fields-wrap]').querySelectorAll(`[data-display-target]`));
            blockList.forEach(block => {
                let displayValueArr = block.dataset.displayTarget.split(',');
              displayValueArr = displayValueArr.map(value => value.trim());
               
                if(displayValueArr.indexOf(value) != -1){
                  block.classList.remove('d-none');
                  
              } else {
                  block.classList.add('d-none');
              }
        });
    
    }
    
    async   function sendInfo(e){

        e.preventDefault();
        let trg = e.currentTarget,
            url = trg.dataset.url,
            data = trg.dataset.value ? trg.dataset.value : trg.value,
          resp = await  sendRequest({url, method: "POST", body: $.param({data: data})});
        
        console.log(resp);

    }
      
    async   function sendBox(e){

        e.preventDefault();
        let trg = e.currentTarget,
            url = trg.dataset.url,
            data = trg.dataset.value ? trg.dataset.value : trg.checked,
          resp = await  sendRequest({url, method: "POST", body: $.param({data: data})});
        
        console.log(resp);
    
    }
        
    async function sendRequest({url, ...options}) {
        try {
            let meta = document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                response;
            if (!options.method) {
                options.method = 'POST';
            }
            options.headers = {
                "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
                // 'Content-Type': 'application/json',
                "Content-Type": "application/x-www-form-urlencoded;  charset=UTF-8",
                // "Content-Type": "application/json; charset=utf-8",
                // "dataType": "json",
                'x-csrf-token': meta,
                "X-Requested-With": "XMLHttpRequest"
            };
    
    
            response = await fetch(url, options);
            if (response.ok) {
                return await response.text();
            }
            return false;
        } catch (e) {
            console.log(e);
        }
    }
JS
);

?>
<?php if(\Yii::$app->session->getFlash('profile')):?>
<?php $this->registerJS(<<<JS
        window.showNotif('Изменения успешно сохранены', 'success', 'top-center', 4500);
        
JS
);?>
<?php \Yii::$app->session->removeAllFlashes(); endif;?>
<div class="uk-grid uk-child-top-gap">
    <!--<div class="uk-width-1-1 uk-flex uk-flex-between uk-flex-middle main-head">
        <ul class="uk-breadcrumb">
            <li><a href="">Главная</a></li>
            <li><span>Профиль</span></li>
        </ul>
    </div>
    <div class="uk-width-auto">
        <div class="nav-cabinet">
            <li class="nav-cabinet__item "><a href="personal-cabinet.html"> Личный кабинет</a></li>
            <li class="nav-cabinet__item "><a href="#">Покупки/продажи</a></li>
            <li class="nav-cabinet__item"><a href="content-cabinet.html">Контент</a></li>
            <li class="nav-cabinet__item"><a href="#">Сообщения</a></li>
            <li class="nav-cabinet__item"><a href="#">Уведомления</a></li>
            <li class="nav-cabinet__item active"><a href="#">Профиль</a></li>
            <li class="nav-cabinet__item"><a href="#">Настройки</a></li>
        </div>
    </div>-->

    <?= \frontend\widgets\UserMenuWidget::widget() ?>

    <div class="cabinet-right uk-width-expand">
            <div class="profile-table">
              <div class="profile-top is-desktop">
                <div class="photo-wrap">
                    <div class="user-photo">
                        <a class="edit-photo" href="<?=Url::to(['change-photo'])?>"><img src="/images/cabinet/icon/edit.svg" alt=""></a>
                        <?php if(($avatar = \Yii::$app->user->identity->photo) && file_exists("./images/users/photo/$avatar")):?>
                            <img src="/images/users/photo/<?=$avatar?>" alt="" class="avatar">
                        <?php else:?>
                            <img src="/images/cabinet/photo-user.png" alt="" class="avatar">
                        <?php endif?>
                    </div>
                </div>
                <div class="right-top-profile">
                    <div class="title-big">Мой профиль</div>
                    <div class="title-small">БАЗОВАЯ ИНФОРМАЦиЯ</div>
                </div>
            </div>
<!--
            <?php /*\yii\widgets\Pjax::begin(['id' => 'pref'])*/?>
            <div class="profile-group">
                <div class="title-settings t-center">
                    <div class="title-small">Предпочтения</div>
                    <button class="edit-btn">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.2393 0.817064L11.4236 2.63276H2.5657V15.3426H15.2756V6.48476L17.0913 4.66907V16.2505C17.0913 16.4913 16.9956 16.7222 16.8254 16.8924C16.6551 17.0627 16.4242 17.1583 16.1834 17.1583H1.65785C1.41707 17.1583 1.18616 17.0627 1.0159 16.8924C0.845648 16.7222 0.75 16.4913 0.75 16.2505V1.72491C0.75 1.48414 0.845648 1.25322 1.0159 1.08297C1.18616 0.912712 1.41707 0.817064 1.65785 0.817064H13.2393ZM16.6237 0L17.9083 1.28551L9.5634 9.63046L8.28151 9.63318L8.2797 8.34676L16.6237 0Z" fill="#1C1C1C" />
                        </svg>
                    </button>
                </div>
                <div id="preferences">
                    <?/*=$this->render('_preferences', compact('userPref', 'category'))*/?>

                    <?php /*if($preferences): $form = \yii\widgets\ActiveForm::begin([
//                        'action' => ['preferences'],
                        'id' => 'form-subcat',
                        'options' => ['data-pjax' => true]
                    ])*/?>
                        <div class="product-group-wrap">
                            <?php /*foreach ($preferences as $preference):*/?>
                                <div class="product-group__item">
                                    <div class="l-section"><?/*=$preference['label']*/?></div>
                                    <div class="r-section"><?/*=$form->field($preferencesForm, $preference['field'])
//                                            ->checkboxList($preference['list'])
                                            ->widget(Select2::class, [
//                        'name' => 'state_10',
                                                'data' => $preference['list'],
                                                'options' => [
                                                    'placeholder' => 'Select provinces ...',
                                                    'multiple' => true
                                                ],
                                            ])
                                            ->label(false)*/?></div>
                                </div>
                            <?php /*endforeach;*/?>
                        </div>
                        <div class="apply-group" style="display: block">
                            <?/*=Html::submitButton('
                                <svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16.7186 0.896273L16.5609 0.744059L16.4032 0.896338L7.21829 9.76955L2.34486 5.06204L2.18717 4.90973L2.02949 5.06204L0.592316 6.45028L0.423322 6.61353L0.592316 6.77677L7.06061 13.0248L7.21829 13.1772L7.37598 13.0248L18.1568 2.61106L18.3259 2.44776L18.1567 2.28452L16.7186 0.896273Z" fill="#1C1C1C" stroke="#1C1C1C" stroke-width="0.453924" />
                                </svg>
                                <span>Применить</span>
                            ')*/?>
                        </div>
                    <?php /*\yii\widgets\ActiveForm::end(); endif;*/?>
                </div>
            </div>
            <?php /*\yii\widgets\Pjax::end()*/?>
                -->
           <!-- <div class="profile-group">
                <div class="title-settings t-center">
                  <div class="title-small">РАЗМЕРЫ</div>
                  <button class="edit-btn">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M13.2393 0.817064L11.4236 2.63276H2.5657V15.3426H15.2756V6.48476L17.0913 4.66907V16.2505C17.0913 16.4913 16.9956 16.7222 16.8254 16.8924C16.6551 17.0627 16.4242 17.1583 16.1834 17.1583H1.65785C1.41707 17.1583 1.18616 17.0627 1.0159 16.8924C0.845648 16.7222 0.75 16.4913 0.75 16.2505V1.72491C0.75 1.48414 0.845648 1.25322 1.0159 1.08297C1.18616 0.912712 1.41707 0.817064 1.65785 0.817064H13.2393ZM16.6237 0L17.9083 1.28551L9.5634 9.63046L8.28151 9.63318L8.2797 8.34676L16.6237 0Z" fill="#1C1C1C" />
                    </svg>
                  </button>
                </div>
                <div class="product-group-wrap">
                  <div class="product-group__item">
                    <div class="l-section"> Одежда </div>
                    <div class="r-section"> M </div>
                  </div>
                  <div class="product-group__item">
                    <div class="l-section"> Обувь </div>
                    <div class="r-section"> 42, 42.5 </div>
                  </div>
                </div>
                <div class="apply-group">
                  <button>
                    <svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M16.7186 0.896273L16.5609 0.744059L16.4032 0.896338L7.21829 9.76955L2.34486 5.06204L2.18717 4.90973L2.02949 5.06204L0.592316 6.45028L0.423322 6.61353L0.592316 6.77677L7.06061 13.0248L7.21829 13.1772L7.37598 13.0248L18.1568 2.61106L18.3259 2.44776L18.1567 2.28452L16.7186 0.896273Z" fill="#1C1C1C" stroke="#1C1C1C" stroke-width="0.453924" />
                    </svg>
                    <span>Применить</span>
                  </button>
                </div>
              </div>-->
              <div class="profile-group">
                <div class="title-settings t-center">
                    <div class="title-small">Уведомления</div>
                    <!--<button class="edit-btn">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.2393 0.817064L11.4236 2.63276H2.5657V15.3426H15.2756V6.48476L17.0913 4.66907V16.2505C17.0913 16.4913 16.9956 16.7222 16.8254 16.8924C16.6551 17.0627 16.4242 17.1583 16.1834 17.1583H1.65785C1.41707 17.1583 1.18616 17.0627 1.0159 16.8924C0.845648 16.7222 0.75 16.4913 0.75 16.2505V1.72491C0.75 1.48414 0.845648 1.25322 1.0159 1.08297C1.18616 0.912712 1.41707 0.817064 1.65785 0.817064H13.2393ZM16.6237 0L17.9083 1.28551L9.5634 9.63046L8.28151 9.63318L8.2797 8.34676L16.6237 0Z" fill="#1C1C1C" />
                        </svg>
                    </button>-->
                </div>
                <div class="product-group-wrap">
                    <div class="product-group__item">
                        <div class="l-section">
                            <span> Получать уведомления о товарах отвечающих заданным параметрам</span>
                            <label class="uk-switch">
                                <?=Html::checkbox('test', false, ['data-send-info-checkbox'=>"", 'data-url'=> Url::to(['/site/index'])])?>
                                <div class="uk-switch-slider"></div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="apply-group">
                    <button>
                        <svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.7186 0.896273L16.5609 0.744059L16.4032 0.896338L7.21829 9.76955L2.34486 5.06204L2.18717 4.90973L2.02949 5.06204L0.592316 6.45028L0.423322 6.61353L0.592316 6.77677L7.06061 13.0248L7.21829 13.1772L7.37598 13.0248L18.1568 2.61106L18.3259 2.44776L18.1567 2.28452L16.7186 0.896273Z" fill="#1C1C1C" stroke="#1C1C1C" stroke-width="0.453924" />
                        </svg>
                        <span>Применить</span>
                    </button>
                </div>
              </div>
              <div class="profile-group">
                <div class="title-settings t-center">
                    <div class="title-small">БАЗОВАЯ ИНФОРМАЦиЯ</div>
<!--                    <button class="edit-btn">-->
<!--                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">-->
<!--                            <path d="M13.2393 0.817064L11.4236 2.63276H2.5657V15.3426H15.2756V6.48476L17.0913 4.66907V16.2505C17.0913 16.4913 16.9956 16.7222 16.8254 16.8924C16.6551 17.0627 16.4242 17.1583 16.1834 17.1583H1.65785C1.41707 17.1583 1.18616 17.0627 1.0159 16.8924C0.845648 16.7222 0.75 16.4913 0.75 16.2505V1.72491C0.75 1.48414 0.845648 1.25322 1.0159 1.08297C1.18616 0.912712 1.41707 0.817064 1.65785 0.817064H13.2393ZM16.6237 0L17.9083 1.28551L9.5634 9.63046L8.28151 9.63318L8.2797 8.34676L16.6237 0Z" fill="#1C1C1C" />-->
<!--                        </svg>-->
<!--                    </button>-->
                </div>
                <?php $form = \yii\widgets\ActiveForm::begin(['id' => 'base-informations', 'options' => ['data-pjax' => true]]);?>
                <div class="product-group-wrap">
                    <div class="product-group__item">
                        <div class="l-section"> Никнейм </div>
                        <div class="r-section"><?=$form->field($baseInfoForm, 'username')->textInput(['placeholder' => '', 'class' => 'form__input form__input-no-padding'])->label(false)?> </div>
                    </div>
                    <div class="product-group__item">
                        <div class="l-section"> Имя Фамилия </div>
                        <div class="r-section"> <?=$form->field($baseInfoForm, 'full_name')->textInput(['placeholder' => '', 'class' => 'form__input form__input-no-padding'])->label(false)?> </div>
                    </div>
                    <div class="product-group__item">
                        <div class="l-section"> Пол </div>
                        <div class="r-section"> <?=$form->field($baseInfoForm, 'sex')->dropDownList([5 => 'Мужчина', 10 => 'Женщина'], ['class' => 'form__select', 'id' => 'category-id', 'prompt' => 'пол'])->label(false) ?></div>
                    </div>
                    <div class="product-group__item">
                        <div class="l-section"> Дата рождения </div>
                        <div class="r-section"> <?=$form->field($baseInfoForm, 'date_of_birth')->textInput(['placeholder' => '', 'class' => 'form__input form__input-no-padding'])->label(false)?> </div>
                    </div>
                    <div class="product-group__item">
                        <div class="l-section"> Валюта </div>
                        <div class="r-section"> <?=$form->field($baseInfoForm, 'currency')->dropDownList([1 => 'EUR'], ['class' => 'form__select', 'id' => 'category-id', 'prompt' => 'валюта'])->label(false) ?> </div>
                    </div>
                    <div class="product-group__item">
                        <div class="l-section"> Язык интерфейса </div>
                        <div class="r-section"> <?=$form->field($baseInfoForm, 'language')->dropDownList([1 => 'русский'], ['class' => 'form__select', 'id' => 'category-id', 'prompt' => 'выбирите язык'])->label(false) ?> </div>
                    </div>
                    <div class="product-group__item">
                        <div class="l-section"> Биография </div>
                        <div class="r-section"> <?=$form->field($baseInfoForm, 'biography')->textarea(['placeholder' => 'Немного о себе...', 'class' => 'form__textarea form__textarea-no-padding'])->label(false)?> </div>
                    </div>
                </div>
                <div class="apply-group" style="display: block">
                    <?=Html::submitButton('
                        <svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.7186 0.896273L16.5609 0.744059L16.4032 0.896338L7.21829 9.76955L2.34486 5.06204L2.18717 4.90973L2.02949 5.06204L0.592316 6.45028L0.423322 6.61353L0.592316 6.77677L7.06061 13.0248L7.21829 13.1772L7.37598 13.0248L18.1568 2.61106L18.3259 2.44776L18.1567 2.28452L16.7186 0.896273Z" fill="#1C1C1C" stroke="#1C1C1C" stroke-width="0.453924" />
                        </svg>
                        <span>Применить</span>
                    ')?>
                </div>
                <?php \yii\widgets\ActiveForm::end() ?>
            </div>
            <div class="profile-group">
                <?php $form = \yii\widgets\ActiveForm::begin(['id' => 'contact-informations', 'options' => ['data-pjax' => true]]);?>
                <div class="title-settings t-center">
                    <div class="title-small">КОНТАКТНАЯ ИНФОРМАЦиЯ</div>
                </div>
                <div class="product-group-wrap">
                    <div class="product-group__item">
                        <div class="l-section"> Страна </div>
                        <div class="r-section"> <?=$form->field($contactInfoForm, 'country')->textInput(['placeholder' => '', 'class' => 'form__input form__input-no-padding'])->label(false)?> </div>
                    </div>
                    <div class="product-group__item">
                        <div class="l-section"> Город </div>
                        <div class="r-section"> <?=$form->field($contactInfoForm, 'city')->textInput(['placeholder' => '', 'class' => 'form__input form__input-no-padding'])->label(false)?> </div>
                    </div>
                    <div class="product-group__item">
                        <div class="l-section"> E-mail </div>
                        <div class="r-section"> <?=$form->field($contactInfoForm, 'email')->textInput(['placeholder' => '', 'class' => 'form__input form__input-no-padding'])->label(false)?> </div>
                    </div>
                    <div class="product-group__item">
                        <div class="l-section"> Телефон </div>
                        <div class="r-section"> <?=$form->field($contactInfoForm, 'telephone')->textInput(['placeholder' => '', 'class' => 'form__input form__input-no-padding'])->label(false)?> </div>
                    </div>
                    <div class="product-group__item">
                        <div class="l-section"> Адрес доставки </div>
                        <div class="r-section"> <?=$form->field($contactInfoForm, 'delivery_address')->textInput(['placeholder' => '', 'class' => 'form__input form__input-no-padding'])->label(false)?> </div>
                    </div>
                </div>
                <div class="apply-group" style="display: block">
                    <?=Html::submitButton('
                        <svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.7186 0.896273L16.5609 0.744059L16.4032 0.896338L7.21829 9.76955L2.34486 5.06204L2.18717 4.90973L2.02949 5.06204L0.592316 6.45028L0.423322 6.61353L0.592316 6.77677L7.06061 13.0248L7.21829 13.1772L7.37598 13.0248L18.1568 2.61106L18.3259 2.44776L18.1567 2.28452L16.7186 0.896273Z" fill="#1C1C1C" stroke="#1C1C1C" stroke-width="0.453924" />
                        </svg>
                        <span>Применить</span>
                    ')?>
                </div>
                <?php \yii\widgets\ActiveForm::end() ?>
            </div>

        </div>
    </div>
</div>
