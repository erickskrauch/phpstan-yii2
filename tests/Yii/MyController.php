<?php
declare(strict_types=1);

namespace Proget\Tests\PHPStan\Yii2\Yii;

use Yii;
use yii\web\Controller;

final class MyController extends Controller {

    public function actionSimpleFindOperations(): void {
        $modelSingle = Article::find()->andWhere(['foo' => 'bar'])->one();
        if ($modelSingle !== null) {
            $modelSingle->flag = true;
        }

        $modelMany = Article::find()->andWhere(['foo' => 'bar'])->all();
        $modelMany[0]->flag = true;
    }

    public function actionRelations(): void {
        $model1 = new Article();
        $model2 = $model1->getComments()->one();
        $model2->field = 'new value';
    }

    public function actionMy(): void {
        $offsetProp = 'flag';
        $flag = false;
        $record = Article::find()->where(['flag' => Yii::$app->request->post('flag', true)])->one();
        if ($record) {
            $record->flag = false;
            $flag = $record[$offsetProp];
            $record[$offsetProp] = true;
            $record->save();
        }

        $record = Article::findOne(['condition']);
        if ($record) {
            $flag = $record->flag;
            $flag = $record['flag'];
        }

        $record = Article::findBySql('');
        if ($record = $record->one()) {
            $flag = $record->flag;
            $flag = $record['flag'];
        }

        $records = Article::find()->asArray()->where(['flag' => Yii::$app->request->post('flag', true)])->all();
        foreach ($records as $record) {
            $flag = $record['flag'];
        }

        $records = Article::findAll('condition');
        foreach ($records as $record) {
            $flag = $record->flag;
        }

        $records = Article::find()->asArray(false)->where(['condition'])->all();
        foreach ($records as $record) {
            $flag = $record->flag;
            $flag = $record['flag'];
            $record['flag'] = true;
        }

        Yii::$app->response->data = Yii::$app->request->rawBody;

        $guest = Yii::$app->user->isGuest;
        Yii::$app->user->identity->getAuthKey();
        Yii::$app->user->identity->doSomething();

        $flag = Yii::$app->customComponent->flag;

        $objectClass = \SplObjectStorage::class;
        Yii::createObject($objectClass)->count();
        Yii::createObject(\SplObjectStorage::class)->count();
        Yii::createObject('SplObjectStorage')->count();
        Yii::createObject(['class' => '\SplObjectStorage'])->count();
        Yii::createObject(static function(): \SplObjectStorage {
            return new \SplObjectStorage();
        })->count();

        (int)Yii::$app->request->headers->get('Content-Length');
        (int)Yii::$app->request->headers->get('Content-Length', 0, true);
        $values = Yii::$app->request->headers->get('X-Key', '', false);
        reset($values);
    }

}
