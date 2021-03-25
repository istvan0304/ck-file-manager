File upload manager for Yii2 CK editor
=================

Requirements
------------

- php >=7.2
- mySQL >=5.7

Installation
------------
The preferred way to install this extension is through composer.

- Run

  $ php composer.phar require istvan0304/ckfilemanager "dev-master"

or add:

        "istvan0304/ckfilemanager": "dev-master"

to the require section of your application's composer.json file.

- Run the migrate to create the database table

        yii migrate --migrationPath=@istvan0304/ckfilemanager/migrations

- Add new modules section to your configuration file:

        'modules' => [
        	'ckfilemanager' => [
                        'class' => 'istvan0304\ckfilemanager\Module'
                    ]
        ],

- Add a new component in components section of your configuration file:

        'ckfilemanager' => [
                    'class' => 'istvan0304\ckfilemanager\components\CkFileManagerComponent',
                    'useOriginalFilename' => false,     		     //use filename (seo friendly) or use a hash
                    'uploadPath' => 'uploads/files',                 //set upload path (default /uploads)
                    'allowDuplicateFiles' => false,                  //Let you to upload an files more than one times (default: false)
                ],

Usage for images
------------

For using the filebrowser in CKEditor add the filebrowserImageBrowseUrl to the clientOptions of the CKEditor widget.
Tested only with CKEditor from 2amigOS.

        use dosamigos\ckeditor\CKEditor;
        
        <?= $form->field($model, 'text')->widget(CKEditor::class, [
                'options' => ['rows' => 6],
                'preset' => 'advanced',
                'clientOptions' => [
                        'filebrowserImageBrowseUrl' => yii\helpers\Url::to(['ckfilemanager/ck-file/image-manager', 'view-mode'=>'iframe', 'select-type'=>'ckeditor']),
                        'filebrowserBrowseUrl' => yii\helpers\Url::to(['ckfilemanager/ck-file/file-manager', 'view-mode'=>'iframe', 'select-type'=>'ckeditor']),
                    ],
            ]);
            ?>

Access
------------
if use rbac set access:

        'as access' => [
                'class' => 'mdm\admin\components\AccessControl',
                'allowActions' => [
                    'ckfilemanager/ck-file/get-file',
                    'ckfilemanager/ck-file/preview-thumbnail'
                ]
            ],