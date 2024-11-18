<?php


namespace frontend\models;


use common\models\Brands;
use common\models\Category;
use common\models\Colors;
use common\models\GoodColorSizes;
use common\models\Goods;
use common\models\GoodsColors;
use common\models\GoodsHashtags;
use common\models\GoodsPhotos;
use common\models\Hashtags;
use common\models\Offers;
use common\models\RequestsPhotos;
use common\models\Sections;
use common\models\Sizes;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\validators\Validator;
use yii\web\UploadedFile;

class GoodForm extends Model
{

    const SCENARIO_PUBLISH = 'publish';

    public $section;
    public $category;
    public $subcategory;
    public $shop; //Магазин-продавец???
    public $brand; //заполняем вручную??
    public $good_name;

    public $good_id;

    public $documents;
    public $images;

    public $old_price;
    public $new_price;
    public $currency;

    public $color;
//    public $size_category;
    public $sizes;
    public $quantity;

    public $hashtags;
    public $description;

    public $data_tags;

    public $request_images;

    private $js_req_img = [];
    private $preview_req_img = [];
    private $config_req_img = [];

    public function rules()
    {
        return [
            [[
                'section', 'category', 'subcategory',
                'brand',
                'good_name',
//                'images',
                'new_price', 'currency',
//                'color', 'sizes', 'quantity'
                'hashtags', 'description',
            ], 'required', 'on' => self::SCENARIO_PUBLISH],

            [['section', 'category', 'subcategory', 'brand', /*'color', 'sizes', 'quantity',*/ 'shop', 'currency', 'good_id'], 'integer'],
            [['good_name', 'description', 'hashtags', 'request_images'], 'string'],
            [['old_price', 'new_price'], 'number'],
            [['images'], 'file', 'extensions' => ['png', 'jpg', 'jpeg'], 'maxFiles' => 5],
            [['sizes', 'color', 'quantity', 'data_tags', 'js_req_img', 'preview_req_img', 'config_req_img'], 'safe'],

//            [['sizes', 'color', 'quantity',], 'each', 'rule' => ['string']],
//            [['quantity'], 'compare', 'operator' => '>=', 'compareValue' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'section' => 'Секция',
            'category' => 'Тип',
            'subcategory' => 'Тов.категория',
            'brand' => 'Бренд',
            'good_name' => 'Название',
            'images' => 'Фотографии',
            'new_price' => 'Цена',
            'currency' => 'Валюта',
            'old_price' => 'Старая цена',
            'color' => 'Цвет',
            'quantity' => 'Количество',
            'sizes' => 'Размеры',
            'hashtags' => 'Ключевые слова',
            'description' => 'Описание',
            'shop' => 'Магазин',
        ];
    }

    public function getErrorsMessages()
    {
        $error_str = '';
        if ($errors = $this->getErrors()) {
            $error_str = 'Ошибка! Вы не можете сохранить черновик:<br/><ul>';
            foreach ($errors as $field => $error_array) {
                $error_str .= "<li>" . array_shift($error_array) . "</li>";
            }
            $error_str .= '</ul>';
        }
        return $error_str;
    }

    public function setValues($good, $goodPhotos, $goodTags, $goodColorSizes)
    {
        $this->section = $good->section_id;
        $this->category = $good->category_id;
        $this->subcategory = $good->subcategory_id;
        $this->brand = $good->brand_id;
        $this->good_name = $good->name;
        $this->description = $good->description;
//        $this->shop = $good->merchant_id;

        $this->old_price = $good->old_price;
        $this->new_price = $good->new_price;
        $this->currency = 1;

        if ($goodColorSizes) {
            foreach ($goodColorSizes as $key => $colorSize) {
                $this->color[$colorSize->id] = $colorSize->color_id;
                $this->sizes[$colorSize->id] = Json::decode($colorSize->size_id);
                $this->quantity[$colorSize->id] = $colorSize->quantity;
            }
        }

        if ($goodTags) {
            foreach ($goodTags as $key => $hashtags) {
                if ($key != 0) $this->data_tags .= ', ';
                $this->data_tags .= "\"{$hashtags->hashtag->tag_name}\"";
            }
//            \Yii::error(print_r($this->getAttributes(), true));
        }

        if ($goodPhotos) {
            foreach ($goodPhotos as $key => $photo) {
                if ($key !== 0) $this->request_images .= ',';
                $this->request_images .= $photo->photo;

                $this->js_req_img[] = $photo->photo;

                $this->preview_req_img[] = Html::img(Url::base() . '/' . GoodsPhotos::IMG_FOLDER . '/' . $photo->photo);

                $this->config_req_img[] = ['key' => $photo->photo];
            }
        }
        \Yii::error(print_r($this->request_images, true));

    }

    public function setValuesOffer($request, $requestPhotos, $requestTags, $requestColorSizes)
    {
        $this->section = $request->section_id;
        $this->category = $request->category_id;
        $this->subcategory = $request->subcategory_id;
        $this->brand = $request->brand_id;
        $this->good_name = $request->name;
        $this->description = $request->description;
        $this->shop = $request->merchant_id;

        if ($requestColorSizes) {
            foreach ($requestColorSizes as $key => $colorSize) {
                $this->color[$key] = $colorSize->color;
                $this->sizes[$key] = explode(',', $colorSize->size);
                $this->quantity[$key] = $colorSize->quantity;
            }
        }

        if ($requestTags) {
            foreach ($requestTags as $key => $hashtags) {
                if ($key != 0) $this->data_tags .= ', ';
                $this->data_tags .= "\"{$hashtags->hashtag->tag_name}\"";
            }
//            \Yii::error(print_r($this->getAttributes(), true));
        }

        if ($requestPhotos) {
            foreach ($requestPhotos as $key => $photo) {
                if ($key !== 0) $this->request_images .= ',';
                $this->request_images .= $photo->photo;

                $this->js_req_img[] = $photo->photo;

                $this->preview_req_img[] = Html::img(Url::base() . '/' . RequestsPhotos::IMG_FOLDER . '/' . $photo->photo);

                $this->config_req_img[] = ['key' => $photo->photo];
            }
        }
    }

    public function get_js_req_data($post_data = null)
    {
        if ($post_data) {
            $arr = explode(',', $post_data);
            $this->js_req_img = [];
            foreach ($arr as $img_name) {
                $this->js_req_img[] = $img_name;
            }
            return $this->js_req_img;
        }
        return $this->js_req_img;
    }

    public function get_preview_req_img($post_data = null)
    {

        if ($post_data) {
            $arr = explode(',', $post_data);
            $this->preview_req_img = [];
            foreach ($arr as $img_name) {
                $this->preview_req_img[] = Html::img(Url::base() . '/' . RequestsPhotos::IMG_FOLDER . '/' . $img_name);
            }
            return $this->preview_req_img;
        }
        return $this->preview_req_img;
    }

    public function get_preview_off_img($post_data = null)
    {
//        \Yii::error('off');
        if ($post_data) {
            $arr = explode(',', $post_data);
            $this->preview_req_img = [];
            foreach ($arr as $img_name) {
                $this->preview_req_img[] = Html::img(Url::base() . '/' . GoodsPhotos::IMG_FOLDER . '/' . $img_name);
            }
            \Yii::error(print_r($this->preview_req_img, true));
            return $this->preview_req_img;
        }
        return $this->preview_req_img;
    }

    public function get_config_req_img($post_data = null)
    {
        if ($post_data) {
            $arr = explode(',', $post_data);
            $this->config_req_img = [];
            foreach ($arr as $img_name) {
                $this->config_req_img[] = ['key' => $img_name];
            }
            return $this->config_req_img;
        }
        return $this->config_req_img;
    }

    public function validate_before_publication()
    {
        $this->validators[] = Validator::createValidator('required', $this,
            ['section', 'category', 'subcategory', 'brand', 'good_name', /*'images',*/ 'new_price', 'currency', 'sizes', 'hashtags', 'description', 'color', 'quantity']);
        if (!$this->request_images) {
            \Yii::error('if');
            $this->validators[] = Validator::createValidator('required', $this, ['images'], ['skipOnEmpty' => false]);
            $this->addError('images', 'Not empty!');
        }
    }

    public function validate_draft()
    {
        $this->validators[] = Validator::createValidator('required', $this,
            ['sizes', 'quantity', 'color']);
        if ($this->color || $this->sizes || $this->quantity) {
            \Yii::error('if');
            if ($this->color) {
                foreach ($this->color as $color) {
                    if ($color) $this->addError('color', 'Обязательно к заполнению!');
                }
            }
            if ($this->sizes) {
                foreach ($this->sizes as $sizes) {
                    if ($sizes) $this->addError('sizes', 'Обязательно к заполнению!');
                }
            }
            if ($this->quantity) {
//                \Yii::error('if');
                foreach ($this->quantity as $quantity) {
//                    \Yii::error('if');
                    if ($quantity) {
//                        \Yii::error('if');
                        $this->addError('color', 'Обязательно к заполнению!');
                        $this->addError('sizes', 'Обязательно к заполнению!');
                    } else $this->addError('quantity', 'Обязательно к заполнению!');
                }
            }

//            \Yii::error(print_r($this->validators, true));
        }
    }

    public function getCat()
    {
        return ArrayHelper::map(Category::findAll(['is_active' => 1, 'parent_id' => Category::DEFAULT_PARENT]), 'id', 'name');
    }

    public function getSubCat($parent)
    {
        return ArrayHelper::map(Category::findAll(['is_active' => 1, 'parent_id' => $parent]), 'id', 'name');
    }

    public function getBrands()
    {
        return ArrayHelper::map(Brands::findAll(['is_active' => 1]), 'id', 'name');
    }

    public function getColors()
    {
        return ArrayHelper::map(Colors::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC])->all(), 'id', 'name');
    }

    public function getSizes($parent = Sizes::DEFAULT_PARENT)
    {
        $sizeCat = Sizes::find()->where(['is_active' => 1, 'parent_id' => $parent])->with('children')->all();
        $arr = [];
        foreach ($sizeCat as $cat) {
            if (!$cat->children) continue;
            foreach ($cat->children as $child) {
                $arr[$cat->name][$child->id] = $child->name;
            }
        }
        return $arr;
//        return ArrayHelper::map($sizeCat, 'id', 'name');
    }

    public function getSections()
    {
        return ArrayHelper::map(Sections::findAll(['is_active' => 1]), 'id', 'name');
    }

    public function saveDatas($status = Goods::STATUS_DRAFT, $good = null, $goodPhotos = null, $goodTags = null, $goodColorSizes = null)
    {
        if (!$good) $good = new Goods(['user_id' => \Yii::$app->user->id]);
        $good->section_id = $this->section;
        $good->category_id = $this->category;
        $good->subcategory_id = $this->subcategory;
        $good->brand_id = $this->brand;
        $good->name = $this->good_name;
        $good->status = $status;
        $good->old_price = $this->old_price;
        $good->new_price = $this->new_price;
        $good->description = $this->description;
//            $good->user_id = \Yii::$app->user->id;

//        \Yii::error('save datas');
        $transactions = \Yii::$app->db->beginTransaction();
        if (!$good->save()) {
            \Yii::error('Good errors: ' . print_r($good->getErrors(), true));
            $transactions->rollBack();
            return false;
        }
//        else \Yii::error('saved!');

//        $good_colors = new GoodsColors([
//            'good_id' => $good->id,
//            'color_id' => $this->color
//        ]);

        if ($this->color) {


            foreach ($this->color as $index => $color_id) {
                if (!$color_id || $color_id == '') continue;
                $size_str = Json::encode($this->sizes[$index]);
//                \Yii::error(print_r($this->color, true).PHP_EOL.print_r($size_str, true).PHP_EOL.print_r($this->quantity, true).PHP_EOL);
                if (!$size_str || $size_str == '' || !isset($this->quantity[$index]) || $this->quantity[$index] == '') {
                    \Yii::error('continue');
                    continue;
                }

                if ($goodColorSizes) {
                    $goodColorSizes = ArrayHelper::index($goodColorSizes, 'id');
                    if (isset($goodColorSizes[$index])) $goodColorSizesObj = $goodColorSizes[$index];
                    else $goodColorSizesObj = new GoodColorSizes(['good_id' => $good->id]);
                } else $goodColorSizesObj = new GoodColorSizes(['good_id' => $good->id]);

//                \Yii::error(print_r($this->color, true).PHP_EOL.print_r($size_str, true).PHP_EOL.print_r($this->quantity, true).PHP_EOL);
//                    $goodColorSizes->good_id = $good->id;
//                VarDumper::dump($this->quantity, 10, true);
                $goodColorSizesObj->color_id = $color_id;
                $goodColorSizesObj->size_id = $size_str;
                $goodColorSizesObj->quantity = $this->quantity[$index];
                if (!$goodColorSizesObj->save()) {
                    \Yii::error('Good Color Sizes errors: ' . print_r($goodColorSizesObj->getErrors(), true));
                    $transactions->rollBack();
                    return false;
                }
            }
        }

        if ($hashtags_arr = $this->getTags()) {
            $availableTags = ArrayHelper::index(Hashtags::findAll(['tag_name' => $hashtags_arr]), 'tag_name');
            if ($goodTags) $goodTags = ArrayHelper::index($goodTags, 'tag_id');
            foreach ($hashtags_arr as $tag_name) {
                if (array_key_exists($tag_name, $availableTags)) {
                    if ($goodTags && array_key_exists($availableTags[$tag_name]->id, $goodTags)) {
                        continue;
                    }
                    $newGoodTag = new GoodsHashtags([
                        'good_id' => $good->id,
                        'tag_id' => $availableTags[$tag_name]->id
                    ]);
                    if (!$newGoodTag->save()) {
                        \Yii::error('New Good Tag errors: ' . print_r($newGoodTag->getErrors(), true));
                        $transactions->rollBack();
                        return false;
                    }
                    continue;
                }

                $newHashtag = new Hashtags([
                    'tag_name' => $tag_name
                ]);
                if (!$newHashtag->save()) {
                    \Yii::error('NewTag errors: ' . print_r($newHashtag->getErrors(), true));
                    $transactions->rollBack();
                    return false;
                }
                $newGoodTag = new GoodsHashtags([
                    'good_id' => $good->id,
                    'tag_id' => $newHashtag->id
                ]);
                if (!$newGoodTag->save()) {
                    \Yii::error('New Good Tag errors: ' . print_r($newGoodTag->getErrors(), true));
                    $transactions->rollBack();
                    return false;
                }
            }
        }

        if ($photos = UploadedFile::getInstances($this, 'images')) {
            $path = \Yii::getAlias('@frontend') . '/web' . '/' . GoodsPhotos::IMG_FOLDER;
            $this->isDir($path);
            foreach ($photos as $file) {
                $new_img_name = \Yii::$app->security->generateRandomString(25);
                if ($file->saveAs("$path/$new_img_name.{$file->extension}")) {
                    $good_photos = new GoodsPhotos([
                        'good_id' => $good->id,
                        'photo' => "$new_img_name.{$file->extension}"
                    ]);
                    $good_photos->save();
                }
            }
        }
        if ($goodPhotos) {
            $images = '';
            if ($this->request_images) {
                $images = explode(',', $this->request_images);
            }
            foreach ($goodPhotos as $photoObj) {
                if ($images && in_array($photoObj->photo, $images)) continue;
                $photoObj->delete();
            }
        }

        $transactions->commit();
        return $good->id;
    }

    public function saveDataOffer($request, $status = Goods::STATUS_DRAFT)
    {
        $good = new Goods([
            'section_id' => $this->section,
            'category_id' => $this->category,
            'subcategory_id' => $this->subcategory,
            'brand_id' => $this->brand,
            'name' => $this->good_name,
            'status' => $status,
            'old_price' => $this->old_price,
            'new_price' => $this->new_price,
            'description' => $this->description,
            'user_id' => \Yii::$app->user->id
        ]);
        $transactions = \Yii::$app->db->beginTransaction();
        if (!$good->save()) {
            \Yii::error('Good errors: ' . print_r($good->getErrors(), true));
            $transactions->rollBack();
            return false;
        }

//        $good_colors = new GoodsColors([
//            'good_id' => $good->id,
//            'color_id' => $this->color
//        ]);
        foreach ($this->color as $index => $color_id) {
            $size_str = Json::encode($this->sizes[$index]);
            $good_color_sizes = new GoodColorSizes([
                'good_id' => $good->id,
                'color_id' => $color_id,
                'size_id' => $size_str,
                'quantity' => $this->quantity[$index]
            ]);
            if (!$good_color_sizes->save()) {
                \Yii::error('Good Color Sizes errors: ' . print_r($good_color_sizes->getErrors(), true));
                $transactions->rollBack();
                return false;
            }
        }

        $hashtags_arr = $this->getTags();
        $availableTags = ArrayHelper::index(Hashtags::findAll(['tag_name' => $hashtags_arr]), 'tag_name');
        foreach ($hashtags_arr as $tag_name) {
            if (array_key_exists($tag_name, $availableTags)) {
                $newGoodTag = new GoodsHashtags([
                    'good_id' => $good->id,
                    'tag_id' => $availableTags[$tag_name]->id
                ]);
                if (!$newGoodTag->save()) {
                    \Yii::error('New Good Tag errors: ' . print_r($newGoodTag->getErrors(), true));
                    $transactions->rollBack();
                    return false;
                }
                continue;
            }
            $newHashtag = new Hashtags([
                'tag_name' => $tag_name
            ]);
            if (!$newHashtag->save()) {
                \Yii::error('NewTag errors: ' . print_r($newHashtag->getErrors(), true));
                $transactions->rollBack();
                return false;
            }

            $newGoodTag = new GoodsHashtags([
                'good_id' => $good->id,
                'tag_id' => $newHashtag->id
            ]);
            if (!$newGoodTag->save()) {
                \Yii::error('New Good Tag errors: ' . print_r($newGoodTag->getErrors(), true));
                $transactions->rollBack();
                return false;
            }
        }

        $photos = UploadedFile::getInstances($this, 'images');
        $path = \Yii::getAlias('@frontend') . '/web' . '/' . GoodsPhotos::IMG_FOLDER;
        $this->isDir($path);
        foreach ($photos as $file) {
            $new_img_name = \Yii::$app->security->generateRandomString(25);
            if ($file->saveAs("$path/$new_img_name.{$file->extension}")) {
                $good_photos = new GoodsPhotos([
                    'good_id' => $good->id,
                    'photo' => "$new_img_name.{$file->extension}"
                ]);
                $good_photos->save();
            }
        }
        if ($this->request_images) {
            $images = explode(',', $this->request_images);
            foreach ($images as $img) {
                $path = \Yii::getAlias('@frontend') . '/web' . '/' . RequestsPhotos::IMG_FOLDER;
                $new_path = \Yii::getAlias('@frontend') . '/web' . '/' . GoodsPhotos::IMG_FOLDER;
                if (file_exists($path . '/' . $img) && copy(($path . '/' . $img), ($new_path . '/' . \Yii::$app->user->id . '_' . $img))) {
                    $good_photos = new GoodsPhotos([
                        'good_id' => $good->id,
                        'photo' => \Yii::$app->user->id . '_' . $img
                    ]);
                    $good_photos->save();
                }
            }
        }

        $offer_to_request = new Offers(['user_id' => \Yii::$app->user->id, 'request_id' => (int)$request, 'good_id' => $good->id]);

        $offer_to_request->save();
//        \Yii::error(print_r($offer_to_request->getErrors(), true));

        $transactions->commit();
        return $good->id;
    }

    public function getTags()
    {
        return $this->hashtags ? explode(',', str_replace('#', '', $this->hashtags)) : false;
    }

    private function isDir($path)
    {
        if (!is_dir($path)) {
            \Yii::info('Create dir: ' . $path);
            mkdir($path, 0777, true);
        }
    }


}
