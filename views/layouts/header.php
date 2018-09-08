<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
?>

<header class="main-header">

    <?= Html::a('<span class="logo-mini"><img src = "'. Yii::$app->request->baseUrl . '/images/coop_logo.png" /></span><span class="logo-lg"><img src = "'. Yii::$app->request->baseUrl . '/images/coop_logo.png" /> DILG-XI EMPC</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>
    <?php
    if(!Yii::$app->user->isGuest){
        ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <?php
        echo yii\bootstrap\Nav::widget(
            [
                'options' => ['class' => 'nav navbar-nav'],
                'items' => [
                    [
                        'label'     => 'Home',
                        'url'       => Yii::$app->homeUrl
                    ],
                    [
                        'label'     => 'Profile',
                        'url'       => ['member/view/'.\Yii::$app->user->identity->member->id], 
                        'visible'   => Yii::$app->user->identity->checkUserAccess("_member_profile_", "_view") || $_GET['member_id'] == Yii::$app->user->identity->member->id
                    ],
                    [
                        'label'     => 'Member',
                        'items'      => [
                            [
                                'label'     => 'List', 
                                'url'       => ['member/user/list'], 
                                'visible'   => Yii::$app->user->identity->checkUserAccess("_member_profile_", "_view")
                            ],
                            [
                                'label'     => 'Create', 
                                'url'       => ['member/user/create'], 
                                'visible'   => Yii::$app->user->identity->checkUserAccess("_member_profile_", "_add")
                            ],

                        ],
                        'visible'   => Yii::$app->user->identity->checkUserAccess("_member_profile_", "_view")
                    ],
                    [
                        'label'     => 'Loan Account',
                        'items'      => [
                            [
                                'label'     => 'Evaluation', 
                                'url'       => ['loan/'], 
                                'visible'   => Yii::$app->user->identity->checkUserAccess("_loan_account_", "_add")
                            ],
                            [
                                'label'     => 'List', 
                                'url'       => ['loan/list'], 
                                'visible'   => Yii::$app->user->identity->checkUserAccess("_loan_account_", "_view")
                            ],

                        ],
                        'visible'   => Yii::$app->user->identity->checkUserAccess("_loan_account_", "_view")
                    ],
                    [
                        'label'     => 'Savings Account',
                        'items'      => [
                            [
                                'label'     => 'New', 
                                'url'       => ['savings/'], 
                                'visible'   => Yii::$app->user->identity->checkUserAccess("_savings_account_", "_add")
                            ],
                            [
                                'label'     => 'Deposit', 
                                'url'       => ['savings/deposit'], 
                                'visible'   => Yii::$app->user->identity->checkUserAccess("_savings_account_", "_edit")
                            ],
                            [
                                'label'     => 'Wihthdraw', 
                                'url'       => ['savings/withdraw'], 
                                'visible'   => Yii::$app->user->identity->checkUserAccess("_savings_account_", "_edit")
                            ],

                        ],
                        'visible'   => Yii::$app->user->identity->checkUserAccess("_savings_account_", "_view")
                    ],
                    [
                        'label'     => 'Time Deposit',
                        'items'      => [
                            [
                                'label'     => 'New', 
                                'url'       => ['savings/create'], 
                                'visible'   => Yii::$app->user->identity->checkUserAccess("_time_account_", "_add")
                            ],

                        ],
                        'visible'   => Yii::$app->user->identity->checkUserAccess("_time_account_", "_view")
                    ],
                    [
                        'label'     => 'Reports',
                        'url'       => '#', 
                        'visible'   => Yii::$app->user->identity->checkUserAccess("_reports_", "_view")
                    ],
                ],
            ]
        );
        ?>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">

                <!-- Tasks: style can be found in dropdown.less -->
                <li class="dropdown tasks-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-flag-o"></i>
                        <span class="label label-danger"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">Important Notice</li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <ul class="menu">
                                <li><!-- Task item -->
                                    <a href="#">
                                        <h3>
                                            Loan Overdue
                                            <small class="pull-right">April 1, 2018</small>
                                        </h3>
                                        <div class="progress xs">
                                            <div class="progress-bar progress-bar-aqua" style="width: 20%"
                                                 role="progressbar" aria-valuenow="20" aria-valuemin="0"
                                                 aria-valuemax="100">
                                                <span class="sr-only">20% Complete</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <!-- end task item -->
                            </ul>
                        </li>
                    </ul>
                </li>
                <!-- User Account: style can be found in dropdown.less -->

                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <div class = "user-image">
                            <div class="circle-avatar" style = "background-image : url('<?= \Yii::$app->user->identity->member->image_path ?>')"></div>
                        </div>
                        <span class="hidden-xs"><?php echo  \Yii::$app->user->identity->member->first_name . " " . \Yii::$app->user->identity->member->last_name; ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <div class = "dropdown-user-image">
                                <div class="circle-avatar" style = "background-image : url('<?= \Yii::$app->user->identity->member->image_path ?>')"></div>
                            </div>

                            <p>
                                <?php echo  \Yii::$app->user->identity->member->first_name . " " . \Yii::$app->user->identity->member->last_name; ?> - <?php echo  \Yii::$app->user->identity->member->memberType->description ?>
                                <small>Member since <?php echo date("M d, Y", strtotime(\Yii::$app->user->identity->member->mem_date)); ?></small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <!--<div class="pull-left">
                                <a href="#" class="btn btn-default btn-flat">Profile</a>
                            </div>-->
                            <div class="pull-right">
                                <?= Html::a(
                                    'Sign out',
                                    ['/site/logout'],
                                    ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                ) ?>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    <?php }
    ?>
</header>
