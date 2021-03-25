<?php

namespace istvan0304\filemanager\controllers;

use Yii;
use istvan0304\filemanager\models\CkImageSearch;
use istvan0304\filemanager\assets\CkImageManagerAsset;
use istvan0304\filemanager\components\UploadException;
use istvan0304\filemanager\models\CkImage;
use istvan0304\filemanager\models\CkImageForm;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\imagine\Image;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Class CkFileController
 * @package istvan0304\filemanager\controllers
 */
class CkFileController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET'],
                    'preview-thumbnail' => ['GET'],
                    'get-image' => ['GET'],
                    'delete' => ['POST'],
                    'search' => ['GET'],
                ],
            ],
        ];
    }

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        $ckImageManagerForm = new CkImageForm();
        $ckImages = CkImage::find()->all();
        $this->layout = "layout";
        CkImageManagerAsset::register($this->view);

        foreach ($ckImages as $key => $ckImage) {
            if (!$ckImage->isExistsFile()) {
                unset($ckImages[$key]);
            }
        }

        return $this->render('index', [
            'ckImageManagerForm' => $ckImageManagerForm,
            'ckImages' => $ckImages
        ]);
    }

    /**
     * @param $id
     * @return array
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionGetDetails($id)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $response = [];

            if ($id != null && is_numeric($id)) {
                $ckImageModel = CkImage::findOne(['id' => $id]);

                if ($ckImageModel != null) {
                    $response['success'] = true;
                    $response['template'] = $this->renderPartial('_details', ['ckImageArray' => $ckImageModel->toArray()]);
                } else {
                    $response['success'] = false;
                    $response['message'] = Yii::t('ckimage', 'File not found!');
                }
            } else {
                $response['success'] = false;
            }

            return $response;
        } else {
            throw new NotFoundHttpException(Yii::t('ckimage', 'Page not found!'));
        }
    }

    /**
     * @return string
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ckImageFormModel = new CkImageForm();
        $response = [];
        $successUpload = 0;
        $uploadResponse = '';

        if ($ckImageFormModel->load(Yii::$app->request->post())) {
            $files = UploadedFile::getInstances($ckImageFormModel, 'img_files');

            foreach ($files as $file) {
                $ckImageModel = new CkImage();
                $ckImageModel->img_file = $file;
                $extension = $ckImageModel->img_file->getExtension();
                $uid = uniqid(time(), true);
                $fileName = $uid . '.' . $extension;
                $filePath = $ckImageModel->img_file->tempName;

                try {
                    if ($ckImageModel->img_file->getHasError()) {
                        throw new UploadException($ckImageModel->img_file->error);
                    }

                    $ckImageModel->file_name = $fileName;
                    $ckImageModel->orig_name = $ckImageModel->img_file->name;
                    $ckImageModel->file_hash = hash_file('md5', $filePath);
                    $ckImageModel->mime = $ckImageModel->img_file->type;
                    $ckImageModel->extension = $extension;
                    $ckImageModel->size = $ckImageModel->img_file->size;

                    if ($ckImageModel->save()) {
                        $ckImageModel->thumbnail = Image::thumbnail($filePath, CkImage::THUMBNAIL_WIDTH, CkImage::THUMBNAIL_WIDTH);

                        if ($ckImageModel->upload() && $ckImageModel->uploadThumbnail()) {
                            $response[$ckImageModel->orig_name] = [
                                'success' => true,
                                'class' => 'ck-success',
                                'message' => Yii::t('ckimage', 'File has been uploaded successfully!')
                            ];

                            $successUpload++;
                        } else {
                            $response[$ckImageModel->orig_name] = [
                                'success' => false,
                                'class' => 'ck-error',
                                'message' => Html::errorSummary($ckImageModel)
                            ];

                            $ckImageModel->delete();
                        }
                    } else {
                        $response[$ckImageModel->orig_name] = [
                            'success' => false,
                            'class' => 'ck-error',
                            'message' => Html::errorSummary($ckImageModel)
                        ];
                    }
                } catch (UploadException $e) {
                    $response[$ckImageModel->img_file->name] = [
                        'success' => false,
                        'class' => 'ck-error',
                        'message' => $e->getMessage()
                    ];
                }
            }

            $uploadResponse = $this->renderAjax('_uploadResponse', ['responsesData' => $response, 'filesNumber' => count($files), 'successUpload' => $successUpload]);
        } else {
            $response[Yii::t('ckimage', 'Error!')] = [
                'success' => false,
                'class' => 'ck-error',
                'message' => Yii::t('ckimage', 'An error occured!')
            ];

            $uploadResponse = $this->renderAjax('_uploadResponse', ['responsesData' => $response, 'filesNumber' => 0, 'successUpload' => 0]);
        }

        return $uploadResponse;
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $response = [];
            $post = Yii::$app->request->post();
            $imgId = $post['id'] ?? null;

            if ($imgId != null) {
                $ckImageModel = CkImage::findOne(['id' => $imgId]);

                if ($ckImageModel && $ckImageModel->delete() && $ckImageModel->deleteFile()) {
                    $response['success'] = true;
                }
            }

            return $response;
        } else {
            throw new NotFoundHttpException(Yii::t('ckimage', 'Page not found!'));
        }
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function actionGetImage($id)
    {
        $ckImage = CkImage::findOne($id);

        if ($ckImage) {
            $path = Yii::$app->imagemanager->uploadPath;

            if (!is_file($path . DIRECTORY_SEPARATOR . $ckImage->orig_name) && !is_file($path . DIRECTORY_SEPARATOR . $ckImage->file_name)) {
                throw new \Exception(Yii::t('ckimage', 'File not found!'));
            } else {
                $pointer = null;

                if (is_file($path . DIRECTORY_SEPARATOR . $ckImage->orig_name)) {
                    $imagePath = $path . DIRECTORY_SEPARATOR . $ckImage->orig_name;
                    header('Content-type: ' . mime_content_type($imagePath));
                    header('Content-Length: ' . filesize($imagePath));
                    $pointer = @fopen($imagePath, 'rb');
                } elseif (is_file($path . DIRECTORY_SEPARATOR . $ckImage->file_name)) {
                    $imagePath = $path . DIRECTORY_SEPARATOR . $ckImage->file_name;
                    header('Content-type: ' . mime_content_type($imagePath));
                    header('Content-Length: ' . filesize($imagePath));
                    $pointer = @fopen($imagePath, 'rb');
                }

                if ($pointer) {
                    fpassthru($pointer);
                    exit();
                }
            }
        }

        throw new \Exception(Yii::t('ckimage', 'File not found!'));
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function actionPreviewThumbnail($id)
    {
        $ckImage = CkImage::findOne($id);

        if ($ckImage) {
            $path = Yii::$app->imagemanager->uploadPath . DIRECTORY_SEPARATOR . CkImage::THUMBNAIL_DIRECTORY;

            if (!is_file($path . DIRECTORY_SEPARATOR . CkImage::THUMBNAIL . $ckImage->orig_name) && !is_file($path . DIRECTORY_SEPARATOR . CkImage::THUMBNAIL . $ckImage->file_name)) {
                throw new \Exception(Yii::t('ckimage', 'File not found!'));
            } else {
                $pointer = null;

                if (is_file($path . DIRECTORY_SEPARATOR . CkImage::THUMBNAIL . $ckImage->orig_name)) {
                    $imagePath = $path . DIRECTORY_SEPARATOR . CkImage::THUMBNAIL . $ckImage->orig_name;
                    header('Content-type: ' . mime_content_type($imagePath));
                    header('Content-Length: ' . filesize($imagePath));
                    $pointer = @fopen($imagePath, 'rb');
                } elseif (is_file($path . DIRECTORY_SEPARATOR . CkImage::THUMBNAIL . $ckImage->file_name)) {
                    $imagePath = $path . DIRECTORY_SEPARATOR . CkImage::THUMBNAIL . $ckImage->file_name;
                    header('Content-type: ' . mime_content_type($imagePath));
                    header('Content-Length: ' . filesize($imagePath));
                    $pointer = @fopen($imagePath, 'rb');
                }

                if ($pointer) {
                    fpassthru($pointer);
                    exit();
                }
            }
        }

        throw new \Exception(Yii::t('ckimage', 'File not found!'));
    }

    /**
     * @param $name
     * @return array
     * @throws NotFoundHttpException
     * @throws \Throwable
     */
    public function actionAjaxSearch($name)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $ckImageSearch = new CkImageSearch();
            $className = explode("\\", get_class($ckImageSearch));
            $ckImages = $ckImageSearch->search([end($className) => ['orig_name' => $name]]);
            $response = [];

            if ($ckImages){
                $response['success'] = true;
                $response['result'] = $this->renderPartial('_imageList', ['ckImages' => $ckImages->getModels()]);
            }else{
                $response['success'] = false;
            }

            return $response;
        } else {
            throw new NotFoundHttpException(Yii::t('ckimage', 'Page not found!'));
        }
    }
}
