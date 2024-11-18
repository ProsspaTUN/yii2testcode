<?php


namespace frontend\controllers;


use common\models\Category;
use common\models\Colors;
use common\models\GoodColorSizes;
use common\models\Goods;
use common\models\Offers;
use common\models\Orders;
use common\models\OrdersDelivery;
use common\models\OrdersPaymentMethods;
use common\models\Requests;
use common\models\RequestsColorSizes;
use common\models\RequestsHashtags;
use common\models\RequestsPhotos;
use common\models\Sizes;
use frontend\models\CheckoutForm;
use frontend\models\GoodForm;
use frontend\models\RequestForm;
use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class OffersController extends BaseController{

    const TYPE_REQUEST = 'request';
    const TYPE_GOOD = 'good';
    const TYPE_DRAFT = 'draft';

    public function actionCreateGood(){
        $model = new GoodForm();

        if($this->request->isAjax && $model->load($this->request->post())){
//            $model->validate_before_publication();

            $model->validate_draft();
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if($model->load($this->request->post())){

            if(\Yii::$app->user->isGuest) return $this->redirect(['/site/registration']);

            if($this->request->post('draft')){
                $model->validate_draft();
                if(!$model->validate()) {
                    \Yii::error(print_r($model->getErrors(), true));

                    \Yii::$app->session->setFlash('validate-good', 'errors');
                    return $this->render('create-good', compact('model'));
                }
                $model->saveDatas( Goods::STATUS_DRAFT);
//                \Yii::error(print_r($model->getErrors(), true));
                return $this->redirect(['success', 'type' => self::TYPE_DRAFT]);
            }

            if(!$this->request->post('draft')/* && $this->request->post('draft') == 1*/){
                $model->validate_before_publication();
//                ActiveForm::validate($model);
                if(!$model->validate()) {
                    \Yii::error(print_r($model->getErrors(), true));

                    \Yii::$app->session->setFlash('validate-good', 'errors');
                    return $this->render('create-good', compact('model'));
                }
            }
            if(!$id = $model->saveDatas( Goods::STATUS_PUBLISHED)) throw new \Exception('Ошибка при сохранении товара!', '500');

            return $this->redirect(['success', 'type' => self::TYPE_GOOD, 'id' => $id]);
        }
        return $this->render('create-good', compact('model'));
    }

    public function actionCreateDraft(){
        $model = new GoodForm();
        if($this->request->isAjax && $model->load($this->request->post())){
            if(\Yii::$app->user->isGuest) return $this->redirect(['/site/registration']);
            $model->validate_draft();
            if(!$model->validate()) {
                \Yii::error(print_r($model->getErrors(), true));

                \Yii::$app->session->setFlash('validate-good', 'errors');
//                return $this->render('create-good', compact('model'));
//                throw new \Exception('Ошибка при сохранении товара!', '500');

                return Json::encode(['isDone' => 0, 'errors' => $model->getErrorsMessages()]);
            }
            $model->saveDatas( Goods::STATUS_DRAFT);
//                \Yii::error(print_r($model->getErrors(), true));
//            return $this->redirect(['success', 'type' => self::TYPE_DRAFT]);

            return Json::encode(['isDone' => 1]);
        }
//        return $this->redirect(['/site/index']);
        throw new \Exception('Ошибка при сохранении товара!', '500');
    }

    public function parseErrors($errorsArr){

    }

    public function actionSuccess($id = 0, $type = self::TYPE_GOOD){
        return $this->render('success', compact('type', 'id'));
    }

    /**
     * Экшн получения подкатегорий, по выбранной категории
     * @return string
     */
    public function actionTest(){
        $id = (int)$this->request->post('id');
        if($id > 0 && $subcat = Category::findAll(['is_active' => 1, 'parent_id' => $id])){
            $options = '';
            foreach ($subcat as $subcategory){
                $options .= "<option value=\"$subcategory->id\">$subcategory->name</option>";
            }
            \Yii::error(print_r($options, true));
            return $options;
        }
        return '<option value="">Товарная группа</option>';
    }

    /**
     * Экшн получения размеров, по выбранной категории
     * @return string
     */
    public function actionGetSizes(){
        $id = (int)$this->request->post('id');
        if($id > 0 && $subcat = Sizes::findAll(['is_active' => 1, 'parent_id' => $id])){
            $options = '';
            foreach ($subcat as $subcategory){
                $options .= "<option value=\"$subcategory->id\">$subcategory->name</option>";
            }
            \Yii::error(print_r($options, true));
            return $options;
        }
        return '<option value="">Товарная группа</option>';
    }

    public function actionCreateRequest(){
        $model = new RequestForm();
        if($model->load($this->request->post())){
            if(\Yii::$app->user->isGuest) return $this->redirect(['/site/registration']);
            $model->validate_before_publication();
//                ActiveForm::validate($model);
            if(!$model->validate()) {
                \Yii::error(print_r($model->getErrors(), true));
                return $this->render('create-request', compact('model'));
            }
            if(!$id = $model->saveDatas( Requests::STATUS_PUBLISHED)) throw new \Exception('Ошибка при сохранении запроса!', '500');

            return $this->redirect(['success', 'type' => self::TYPE_REQUEST, 'id' => $id]);
        }

        return $this->render('create-request', compact('model'));
    }

    public function actionCreateOffer($request){
        if(!$requestObj = Requests::findOne(['id' => $request])) throw new \Exception('Такого запроса не существует!', '404');
        if(Offers::find()->where(['user_id' => \Yii::$app->user->id, 'request_id' => $request])->exists()) throw new HttpException( '403','Вы уже отреагировали на этот запрос');
        $requestPhotos = RequestsPhotos::findAll(['requests_id' => $requestObj->id]);
        $requestTags = RequestsHashtags::findAll(['requests_id' => $requestObj->id]);
        $requestColorSizes = RequestsColorSizes::findAll(['requests_id' => $requestObj->id]);

        $model = new GoodForm();
        $model->setValuesOffer($requestObj, $requestPhotos, $requestTags, $requestColorSizes);

        if($model->load($this->request->post())){
            if(\Yii::$app->user->isGuest) return $this->redirect(['/site/registration']);
            if($this->request->post('draft')){
                return $this->redirect(['success']);
            }
//            /*TODO - сделать проверку на параметр "сохранить как черновик", и сохранить все без разбору в базу"*/
//            if(!$this->request->post('draft')/* && $this->request->post('draft') == 1*/){
                $model->validate_before_publication();
////                ActiveForm::validate($model);
                if(!$model->validate()) {
                    \Yii::error(print_r($model->getErrors(), true));
                    return $this->render('create-offer', compact('model'));
                }
//            }
            if(!$id = $model->saveDataOffer($request, Goods::STATUS_PUBLISHED)) throw new \Exception('Ошибка при сохранении товара!', '500');
//
            return $this->redirect(['success', 'type' => self::TYPE_GOOD, 'id' => $id]);
        }
        return $this->render('create-offer', compact('model'));
    }

    public function actionGood($id){
        if(!$good = Goods::findOne(['id' => $id])) throw new NotFoundHttpException('The requested page does not exist.');
        $similar = Goods::find()
            ->where(['status' => Goods::STATUS_PUBLISHED, 'section_id' => $good->section_id, 'category_id' => $good->category_id])
            ->andWhere(['not', ['id' => $good->id]])
            ->all();
        $order = new Orders(['good_id' => $good->id]);
        return $this->render('good', [
            'good' => $good,
            'order' => $order,
            'similar' => $similar,
        ]);
    }

    public function actionRequest($id){
        if(!$good = Requests::find()->where(['id' => $id])->with(['colorsSizes', 'photos'])->one()) throw new NotFoundHttpException('The requested page does not exist.');
        $order = new Orders(['good_id' => $good->id]);
        return $this->render('view', [
            'good' => $good,
            'order' => $order,
        ]);
    }

    public function actionDeleteImg(){
        return true;
    }

    public function actionOrder(){
        if(\Yii::$app->user->isGuest) return $this->redirect(['/site/registration']);

        $order = new Orders();

        if($order->load($this->request->post())){
            if(!$good = Goods::findOne(['id' => (int)$order->good_id])) throw new NotFoundHttpException('The requested page does not exist.');
            $order->quantity = 1;
            $order->user_id = \Yii::$app->user->id;
//            $good_color_sizes = GoodColorSizes::findAll(['good_id' => (int)$good->id, 'color_id' => (int)$order->color_id]);
            $color = Colors::findOne(['id' => $order->color_id]);
            $size = Sizes::findOne(['id' => $order->size_id]);

            $model = new CheckoutForm([
                'good_id' => (int)$order->good_id,
                'color_id' => (int)$order->color_id,
                'size_id' => (int)$order->size_id,
                'quantity' => 1,
                'price' => $good->new_price,
            ]);

            return $this->render('checkout', [
               'model' => $model,
               'good' => $good,
               'color' => $color,
               'size' => $size,
//               'good_color_sizes' => $good_color_sizes,
            ]);
        }
        throw new HttpException('404', 'Что-то пошло не так!');
    }

    public function actionCreateOrder(){
        if(\Yii::$app->user->isGuest) return $this->redirect(['/site/registration']);
        $model = new CheckoutForm();

        if($model->load($this->request->post())){
            $transaction = \Yii::$app->db->beginTransaction();
            $order = new Orders([
//                'user_id' => \Yii::$app->user->id,
                'good_id' => $model->good_id,
                'size_id' => $model->size_id,
                'color_id' => $model->color_id,
                'quantity' => $model->quantity,
//                'price' => $model->price,
                'promo_code' => $model->promo_code,
            ]);
            if(!\Yii::$app->user->isGuest) $order->user_id = \Yii::$app->user->id;
            $order->generateNumber();
            Yii::warning(print_r($model->promo_code, true));
            Yii::warning(print_r($order->getAttributes(), true));

            $good = Goods::findOne(['id' => $order->good_id]);
            $price_good = $good->new_price * $order->quantity;

            $comm_service = 7;
            $comm_author = 3;
            $promo_code = 0;

            $price_service = round((($price_good * $comm_service) / 100), 2);
            $price_author = round((($price_good * $comm_author) / 100), 2);
            $price_promo = round((($price_good * $promo_code) / 100), 2);

            $order->price = $price_good + $price_service + $price_author - $price_promo;
            $order->service_commission = $price_service;
            $order->author_commission = $price_author;
            $order->promo_discount = $price_promo;

            $order->hash = $order->generateHash();

            if(!$order->save()){
                \Yii::error(print_r($order->getErrors(), true));
                $transaction->rollBack();
                throw new HttpException('404', 'Что-то пошло не так');
            }

            $order_delivery = new OrdersDelivery([
               'order_id' => $order->id,
               'delivery_method' => $model->delivery_method,
               'recipient_name' => $model->recipient_name,
               'recipient_email' => $model->recipient_email,
               'recipient_city' => $model->recipient_city,
               'recipient_address' => $model->recipient_address,
               'recipient_index' => $model->recipient_index,
               'recipient_telephone' => $model->recipient_telephone,
            ]);
            Yii::warning(print_r($order_delivery->getAttributes(), true));
            if(!$order_delivery->save()){
                \Yii::error(print_r($order_delivery->getErrors(), true));
                $transaction->rollBack();
                throw new HttpException('404', 'Что-то пошло не так');
            }

            $order_payment = new OrdersPaymentMethods([
                'order_id' => $order->id,
                'payment_method' => $model->pay_with,
                'card_number' => $model->card_number,
                'card_period' => $model->card_period,
                'card_owner_name' => $model->card_owner_name,
                'paypal_addres' => $model->paypal_addres,
            ]);
            Yii::warning(print_r($order_payment->getAttributes(), true));
            if(!$order_payment->save()){
                \Yii::error(print_r($order_payment->getErrors(), true));
                $transaction->rollBack();
                throw new HttpException('404', 'Что-то пошло не так');
            }

            $transaction->commit();
            return $this->redirect(['done',
                'hash' => $order->hash
            ]);
        }
        throw new HttpException('404', 'Что-то пошло не так');
    }

    public function actionDone($hash){
        if(!$order = Orders::findOne(['hash' => $hash])) throw new HttpException('404', 'Что-то пошло не так');
        $order_delivery = OrdersDelivery::findOne(['order_id' => $order->id]);
        $order_payment = OrdersPaymentMethods::findOne(['order_id' => $order->id]);
        return $this->render('done',[
            'order' => $order,
            'order_delivery' => $order_delivery,
            'order_payment' => $order_payment,
        ]);
    }

    public function actionGetFields(){
        $model = new GoodForm();
        $random_id = rand(100, 1000);
        $form = ActiveForm::begin();

        return $this->renderAjax('_add-fields',[
            'model' => $model,
            'random_id' => $random_id,
            'form' => $form,
        ]);
    }

    public function actionGetPromo(){
//        $good_id = ;
//        $price = ;
//
//        $good = Goods::findOne(['id' => $good_id]);
//        $price = $good->new_price * $price;

        return Json::encode([
            'promo_valid' => true,
            'promo_commission' => -5
        ]);
    }
}
