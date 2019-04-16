<?php

use app\models\User;

/* @var $this yii\web\View */

$this->title = 'DILG XI - EMPC';
?>
<div class="site-index">


    <div class="box box-primary direct-chat direct-chat-primary">
        <div class="box-header with-border">
            <h3 class="box-title">DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE SYSTEMS  <?php echo $calendarDate['date'] ?></h3>
        </div>
        <div class="box-body">
            
            <?php 
            if(empty($calendarDate['date']))
            {   
                $link = "";
                if(Yii::$app->user->identity->checkUserAccess("_begin_the_day_","_view")){
                    $link = "<a href = '".Yii::$app->request->baseUrl."/site/beginning-of-day' >here</a>";
                }
            ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-warning"></i> Reminder!!! </h4>
                    System Date is not updated. Please update <?= $link ?>.
                </div>
             	
            <?php 
            }
            
            else
            {
            	?>
              <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-warning"></i> System is up-to-date! </h4>
                You may now proceed to your transactions
              </div>	
            	
            	
            	<?php 
            }
            ?>
            
            
            <div class="row">
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-aqua"><i class="fa fa-fw fa-calendar-check-o"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">ACTIVE SYSTEM DATE</span>
                            <span class="info-box-number"><?= $currentDate ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-green"><i class="fa fa-fw fa-users"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">MEMBERS</span>
                            <span class="info-box-number"><?php echo $countAll; ?></span>
                        </div>

                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-yellow"><i class="fa fa-fw fa-user"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">NEW MEMBERS (2019)</span>
                            <span class="info-box-number"><?php echo $countLastYear; ?></span>
                        </div>

                    </div>
                </div>
            </div>
            <div  class="row">
                <div class="col-md-3">
                    <div class="box box-primary">
                        <div class="box-body box-profile">
                            <img class="profile-user-img img-responsive img-circle" style="width:200px;" src="<?php echo \Yii::$app->request->BaseUrl ?>/images/coop_logo.png" alt="User profile picture">
                            <br/>
                            <ul class="list-group list-group-unbordered">
                                <li class="list-group-item">
                                  <b>Founded</b> <a>March 18, 2006</a>
                                </li>
                                <li class="list-group-item">
                                  <b>Location</b> <a >Matina, Davao City, Davao del Sur</a>
                                </li>
                                <li class="list-group-item">
                                  <b>Contact</b> <a>+639002312311</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title text-green"> <i class="fa fa-fw fa-download"></i> SYSTEM FEATURES</h3>

                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <ul class="products-list product-list-in-box">
                                <li class="item">
                                    <div class="product-img">
                                    
                                    </div>
                                    <div class="product-info">
                                        <a href="javascript:void(0)" class="product-title">SAVINGS DEPOSIT AND WITHDRAWAL FACILITY</a>
                                    </div>
                                </li>

                                <li class="item">
                                    <div class="product-img">
                                    
                                    </div>
                                    <div class="product-info">
                                        <a href="javascript:void(0)" class="product-title">SHARE DEPOSIT FACILITY</a>
                                    </div>
                                </li>

                                <li class="item">
                                    <div class="product-img">
                                    
                                    </div>
                                    <div class="product-info">
                                        <a href="javascript:void(0)" class="product-title">One page paymeny facility</a>
                                    </div>
                                </li>

                                <li class="item">
                                    <div class="product-img">
                                    
                                    </div>
                                    <div class="product-info">
                                        <a href="javascript:void(0)" class="product-title">General Voucher Facility</a>
                                    </div>
                                </li>

                                <li class="item">
                                    <div class="product-img">
                                    
                                    </div>
                                    <div class="product-info">
                                        <a href="javascript:void(0)" class="product-title">Change Password</a>
                                        <span class="product-description"> Allow member to change his/her password. </span>
                                    </div>
                                </li>

                            </ul>
                        </div>
                        <div class="box-footer text-center">
                            <a href="javascript:void(0)" class="uppercase">DILG COOP SYSTEM V.1.0</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
        </div>
    </div>
</div>
 
