<?php

$this->title = 'Личный кабинет';
$this->params['breadcrumbs'][] = [
    'label' => $this->title,
];

use yii\helpers\Url; ?>

<div class="uk-grid uk-child-top-gap" uk-grid>
    <?= \frontend\widgets\UserMenuWidget::widget() ?>

    <div class="cabinet-right uk-width-expand">
        <div class="cabinet-info--top is-desktop">
            <div class="l-section">
                <div class="photo-name-user">
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
                    <div class="name-wrap">
                        <div class="title-block">Личный кабинет</div>
                        <div class="name">
                            <span>Марго Тарасова</span>
                            <a href="#" class="btn btn__black btn__uppercase">подписаться</a>
                        </div>
                    </div>
                </div>
                <div class="cabinet-metrics">
                    <div class="cabinet-metrics--switch aside-filter__item-black">
                        <span class="metrics__item-title">Метрика автора</span>
                        <label class="uk-switch">
                            <input type="checkbox" />
                            <div class="uk-switch-slider"></div>
                        </label>
                        <span class="metrics__item-title">Метрика покупателя</span>
                    </div>
                    <div class="cabinet-metrics--value">
                        <div class="metrics-value_item">
                            <div class="num">425</div>
                            <div class="title">Покупки</div>
                        </div>
                        <div class="metrics-value_item">
                            <a href="#modal-following" uk-toggle>
                                <span class="num">127</span>
                                <span class="title">подписки</span>
                            </a>
                        </div>
                        <div class="metrics-value_item">
                            <a href="#modal-followers" uk-toggle>
                                <span class="num">42</span>
                                <span class="title">Подписчики</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="r-section">
                <div class="r-title">Моя репутация</div>
                <div class="rating-wrap">
                    <div class="rating__item">
                        <div class="star-wrap">
                            <div class="title-star">контент</div>
                            <div class="rate">
                                <input type="radio" id="star1" name="rate" value="1" />
                                <label for="star1" title="text">1 star</label>
                                <input type="radio" id="star2" name="rate" value="2" />
                                <label for="star2" title="text">2 stars</label>
                                <input type="radio" id="star3" name="rate" value="3" checked />
                                <label for="star3" title="text">3 stars</label>
                                <input type="radio" id="star4" name="rate" value="4" />
                                <label for="star4" title="text">4 stars</label>
                                <input type="radio" id="star5" name="rate" value="5" />
                                <label for="star5" title="text">5 stars</label>
                            </div>
                        </div>
                        <a href="#" class="reviews-link">17 отзывов</a>
                    </div>
                    <div class="rating__item">
                        <div class="star-wrap">
                            <div class="title-star">покупки</div>
                            <div class="rate">
                                <input type="radio" id="star11" name="rate1" value="1" checked />
                                <label for="star11" title="text">1 star</label>
                                <input type="radio" id="star21" name="rate1" value="2" />
                                <label for="star21" title="text">2 stars</label>
                                <input type="radio" id="star31" name="rate1" value="3" />
                                <label for="star31" title="text">3 stars</label>
                                <input type="radio" id="star41" name="rate1" value="4" />
                                <label for="star41" title="text">4 stars</label>
                                <input type="radio" id="star51" name="rate1" value="5" />
                                <label for="star51" title="text">5 stars</label>
                            </div>
                        </div>
                        <a href="#" class="reviews-link">2 отзыва</a>
                    </div>
                </div>
            </div>
            <div class="b-section invite-friend">
                <div class="title-intive">
                    <img src="/img/cabinet/icon/money.svg" alt="">
                    <span>пригласи друга и каждый получите купон на кэшбек с покупок</span>
                </div>
                <a href="#" class="link-invite">Пригласить друзей</a>
            </div>
        </div>
        <!-- begin mobile-ser__header -->
        <div class="mobile-ser__header is-mobile">
            <!-- begin mobile-ser__header-top -->
            <div class="mobile-ser__header-top">
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
                <div class="name">
                    <span class="mobile-ser__name">Марго Тарасова</span>
                    <a href="#" class="btn btn__black btn__uppercase">подписаться</a>
                </div>
            </div>
            <!-- end mobile-ser__header-top -->
            <!-- begin mobile-ser__header-item -->
            <div class="mobile-ser__header-item">
                <!-- begin mobile-ser__header-inner-50 -->
                <div class="mobile-ser__header-inner-50">
                    <div class="rating__item">
                        <div class="star-wrap">
                            <div class="title-star">контент</div>
                            <div class="rate">
                                <input type="radio" id="star1" name="rate" value="1" />
                                <label for="star1" title="text">1 star</label>
                                <input type="radio" id="star2" name="rate" value="2" />
                                <label for="star2" title="text">2 stars</label>
                                <input type="radio" id="star3" name="rate" value="3" checked />
                                <label for="star3" title="text">3 stars</label>
                                <input type="radio" id="star4" name="rate" value="4" />
                                <label for="star4" title="text">4 stars</label>
                                <input type="radio" id="star5" name="rate" value="5" />
                                <label for="star5" title="text">5 stars</label>
                            </div>
                        </div>
                        <a href="#" class="reviews-link">17 отзывов</a>
                    </div>
                </div>
                <!-- end mobile-ser__header-inner-50 -->
                <!-- begin mobile-ser__header-inner-50 -->
                <div class="mobile-ser__header-inner-50">
                    <div class="rating__item">
                        <div class="star-wrap">
                            <div class="title-star">покупки</div>
                            <div class="rate">
                                <input type="radio" id="star11" name="rate1" value="1" checked />
                                <label for="star11" title="text">1 star</label>
                                <input type="radio" id="star21" name="rate1" value="2" />
                                <label for="star21" title="text">2 stars</label>
                                <input type="radio" id="star31" name="rate1" value="3" />
                                <label for="star31" title="text">3 stars</label>
                                <input type="radio" id="star41" name="rate1" value="4" />
                                <label for="star41" title="text">4 stars</label>
                                <input type="radio" id="star51" name="rate1" value="5" />
                                <label for="star51" title="text">5 stars</label>
                            </div>
                        </div>
                        <a href="#" class="reviews-link">2 отзыва</a>
                    </div>
                </div>
                <!-- end mobile-ser__header-inner-50 -->
            </div>
            <!-- end mobile-ser__header-item -->
            <!-- begin mobile-ser__header-item -->
            <div class="mobile-ser__header-item">
                <!-- begin mobile-ser__header-inner-25 -->
                <div class="mobile-ser__header-inner-25">
                    <div class="metrics-value_item">
                        <div class="num">425</div>
                        <div class="title">Публикаций</div>
                    </div>
                </div>
                <!-- end mobile-ser__header-inner-25 -->
                <!-- begin mobile-ser__header-inner-25 -->
                <div class="mobile-ser__header-inner-25">
                    <div class="metrics-value_item">
                        <div class="num">425</div>
                        <div class="title">Покупки</div>
                    </div>
                </div>
                <!-- end mobile-ser__header-inner-25 -->
                <!-- begin mobile-ser__header-inner-25 -->
                <div class="mobile-ser__header-inner-25">
                    <div class="metrics-value_item">
                        <a href="#modal-following" uk-toggle>
                            <span class="num">127</span>
                            <span class="title">подписки</span>
                        </a>
                    </div>
                </div>
                <!-- end mobile-ser__header-inner-25 -->
                <!-- begin mobile-ser__header-inner-25 -->
                <div class="mobile-ser__header-inner-25">
                    <div class="metrics-value_item">
                        <a href="#modal-followers" uk-toggle>
                            <span class="num">42</span>
                            <span class="title">Подписчики</span>
                        </a>
                    </div>
                </div>
                <!-- end mobile-ser__header-inner-25 -->
            </div>
            <!-- end mobile-ser__header-item -->
            <!-- begin mobile-ser__header-item -->
            <div class="mobile-ser__header-item">
                <div class="b-section invite-friend">
                    <img src="/img/cabinet/icon/money.svg" alt="">
                    <div class="title-intive">
                        <span>пригласи друга и каждый получите купон на кэшбек с покупок</span>
                        <a href="#" class="link-invite">Пригласить друзей</a>
                    </div>
                </div>
            </div>
            <!-- end mobile-ser__header-item -->
        </div>
        <!-- end mobile-ser__header -->

        <?= $this->render($type == 'likes' ? '_likes' : '_whish_list', [
            'dataProvider' => $dataProvider
        ])?>
</div>
