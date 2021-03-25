<?php

namespace istvan0304\ckfilemanager\controllers;

use Yii;
use istvan0304\ckfilemanager\{assets\CkFileManagerAsset,
    models\CkFile,
    models\CkFileForm,
    models\CkFileSearch,
    components\UploadException,
    models\CkImage};
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\imagine\Image;
use yii\web\{Controller, NotFoundHttpException, Response, UploadedFile};

/**
 * Class CkFileController
 * @package istvan0304\ckfilemanager\controllers
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
                    'image-manager' => ['get'],
                    'file-manager' => ['get'],
                    'preview-thumbnail' => ['get'],
                    'upload' => ['post'],
                    'get-details' => ['get'],
                    'get-file' => ['get'],
                    'delete' => ['post'],
                    'ajax-search' => ['get'],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionImageManager()
    {
        $ckFileManagerForm = new CkFileForm();
        $ckImages = CkFile::find()->where(['type' => CkFile::TYPE_IMAGE])->all();
        $this->layout = "layout";
        CkFileManagerAsset::register($this->view);
        $acceptFiles = 'image/*';

        if($ckImages != null){
            foreach ($ckImages as $key => $ckImage) {
                if (!$ckImage->isExistsFile()) {
                    unset($ckImages[$key]);
                }
            }
        }

        return $this->render('index', [
            'ckFileManagerForm' => $ckFileManagerForm,
            'ckFiles' => $ckImages,
            'acceptFiles' => $acceptFiles
        ]);
    }

    /**
     * @return string
     */
    public function actionFileManager()
    {
        $ckFileManagerForm = new CkFileForm();
        $ckFiles = CkFile::find()->all();
        $this->layout = "layout";
        CkFileManagerAsset::register($this->view);
        $acceptFiles = 'application/pdf, image/jpeg, image/jpg, image/png, .doc, .docx, application/msword, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel';

        if($ckFiles != null){
            foreach ($ckFiles as $key => $ckFile) {
                if (!$ckFile->isExistsFile()) {
                    unset($ckFiles[$key]);
                }
            }
        }

        return $this->render('index', [
            'ckFileManagerForm' => $ckFileManagerForm,
            'ckFiles' => $ckFiles,
            'acceptFiles' => $acceptFiles
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
                $ckFileModel = CkFile::findOne(['id' => $id]);

                if ($ckFileModel != null) {
                    $response['success'] = true;
                    $response['template'] = $this->renderPartial('_details', ['ckFileArray' => $ckFileModel->toArray()]);
                } else {
                    $response['success'] = false;
                    $response['message'] = Yii::t('ckfile', 'File not found!');
                }
            } else {
                $response['success'] = false;
            }

            return $response;
        } else {
            throw new NotFoundHttpException(Yii::t('ckfile', 'Page not found!'));
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
        ini_set("memory_limit", -1);
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ckFileFormModel = new CkFileForm();
        $response = [];
        $successUpload = 0;
        $uploadResponse = '';

        if ($ckFileFormModel->load(Yii::$app->request->post())) {
            $files = UploadedFile::getInstances($ckFileFormModel, 'uploaded_files');

            foreach ($files as $file) {
                $ckFileModel = new CkFile();
                $ckFileModel->uploaded_file = $file;
                $extension = $ckFileModel->uploaded_file->getExtension();
                $uid = uniqid(time(), true);
                $fileName = $uid . '.' . $extension;
                $filePath = $ckFileModel->uploaded_file->tempName;

                try {
                    if ($ckFileModel->uploaded_file->getHasError()) {
                        throw new UploadException($ckFileModel->uploaded_file->error);
                    }

                    $ckFileModel->file_name = $fileName;
                    $ckFileModel->orig_name = $ckFileModel->uploaded_file->name;
                    $ckFileModel->file_hash = hash_file('md5', $filePath);
                    $ckFileModel->mime = $ckFileModel->uploaded_file->type;
                    $ckFileModel->extension = $extension;
                    $ckFileModel->size = $ckFileModel->uploaded_file->size;
                    $ckFileModel->setType();

                    if($ckFileModel->type == CkFile::TYPE_IMAGE){
                        $ckFileModel->thumbnail = Image::thumbnail($filePath, CkFile::THUMBNAIL_WIDTH, CkFile::THUMBNAIL_WIDTH);
                        $ckFileModel->setScenario(CkFile::SCENARIO_IMAGE);
                    }else{
                        $ckFileModel->setScenario(CkFile::SCENARIO_OTHER_FILE);
                    }

                    if ($ckFileModel->save()) {
                        if ($ckFileModel->upload()) {
                            if($ckFileModel->type == CkFile::TYPE_IMAGE){
                                $ckFileModel->uploadThumbnail();
                            }

                            $response[$ckFileModel->orig_name] = [
                                'success' => true,
                                'class' => 'ck-success',
                                'message' => Yii::t('ckfile', 'File has been uploaded successfully!')
                            ];

                            $successUpload++;
                        } else {
                            $response[$ckFileModel->orig_name] = [
                                'success' => false,
                                'class' => 'ck-error',
                                'message' => Html::errorSummary($ckFileModel)
                            ];

                            $ckFileModel->delete();
                        }
                    } else {
                        $response[$ckFileModel->orig_name] = [
                            'success' => false,
                            'class' => 'ck-error',
                            'message' => Html::errorSummary($ckFileModel)
                        ];
                    }
                } catch (UploadException $e) {
                    $response[$ckFileModel->uploaded_file->name] = [
                        'success' => false,
                        'class' => 'ck-error',
                        'message' => $e->getMessage()
                    ];
                }
            }

            $uploadResponse = $this->renderAjax('_uploadResponse', ['responsesData' => $response, 'filesNumber' => count($files), 'successUpload' => $successUpload]);
        } else {
            $response[Yii::t('ckfile', 'Error!')] = [
                'success' => false,
                'class' => 'ck-error',
                'message' => Yii::t('ckfile', 'An error occured!')
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
            $fileId = $post['id'] ?? null;

            if ($fileId != null) {
                $ckFileModel = CkFile::findOne(['id' => $fileId]);

                if ($ckFileModel && $ckFileModel->delete() && $ckFileModel->deleteFile()) {
                    $response['success'] = true;
                }
            }

            return $response;
        } else {
            throw new NotFoundHttpException(Yii::t('ckfile', 'Page not found!'));
        }
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function actionGetFile($id)
    {
        $ckFile = CkFile::findOne($id);

        if ($ckFile) {
            $path = Yii::$app->ckfilemanager->uploadPath;

            if (!is_file($path . DIRECTORY_SEPARATOR . $ckFile->orig_name) && !is_file($path . DIRECTORY_SEPARATOR . $ckFile->file_name)) {
                throw new \Exception(Yii::t('ckfile', 'File not found!'));
            } else {
                $pointer = null;

                if (is_file($path . DIRECTORY_SEPARATOR . $ckFile->orig_name)) {
                    $filePath = $path . DIRECTORY_SEPARATOR . $ckFile->orig_name;
                    header('Content-type: ' . mime_content_type($filePath));
                    header('Content-Length: ' . filesize($filePath));
                    $pointer = @fopen($filePath, 'rb');
                } elseif (is_file($path . DIRECTORY_SEPARATOR . $ckFile->file_name)) {
                    $filePath = $path . DIRECTORY_SEPARATOR . $ckFile->file_name;
                    header('Content-type: ' . mime_content_type($filePath));
                    header('Content-Length: ' . filesize($filePath));
                    $pointer = @fopen($filePath, 'rb');
                }

                if ($pointer) {
                    fpassthru($pointer);
                    exit();
                }
            }
        }

        throw new \Exception(Yii::t('ckfile', 'File not found!'));
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function actionPreviewThumbnail($id)
    {
        $ckImage = CkFile::findOne($id);

        if ($ckImage) {
            $path = Yii::$app->ckfilemanager->uploadPath . DIRECTORY_SEPARATOR . CkFile::THUMBNAIL_DIRECTORY;

            if (!is_file($path . DIRECTORY_SEPARATOR . CkFile::THUMBNAIL . $ckImage->orig_name) && !is_file($path . DIRECTORY_SEPARATOR . CkFile::THUMBNAIL . $ckImage->file_name)) {
                throw new \Exception(Yii::t('ckfile', 'File not found!'));
            } else {
                $pointer = null;

                if (is_file($path . DIRECTORY_SEPARATOR . CkFile::THUMBNAIL . $ckImage->orig_name)) {
                    $imagePath = $path . DIRECTORY_SEPARATOR . CkFile::THUMBNAIL . $ckImage->orig_name;
                    header('Content-type: ' . mime_content_type($imagePath));
                    header('Content-Length: ' . filesize($imagePath));
                    $pointer = @fopen($imagePath, 'rb');
                } elseif (is_file($path . DIRECTORY_SEPARATOR . CkFile::THUMBNAIL . $ckImage->file_name)) {
                    $imagePath = $path . DIRECTORY_SEPARATOR . CkFile::THUMBNAIL . $ckImage->file_name;
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

        throw new \Exception(Yii::t('ckfile', 'File not found!'));
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
            $ckFileSearch = new CkFileSearch();
            $className = explode("\\", get_class($ckFileSearch));
            $ckFiles = $ckFileSearch->search([end($className) => ['orig_name' => $name]]);
            $response = [];

            if ($ckFiles){
                $response['success'] = true;
                $response['result'] = $this->renderPartial('_fileList', ['ckFiles' => $ckFiles->getModels()]);
            }else{
                $response['success'] = false;
            }

            return $response;
        } else {
            throw new NotFoundHttpException(Yii::t('ckfile', 'Page not found!'));
        }
    }
}
