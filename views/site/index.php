<?php

use app\models\User;

/* @var $this yii\web\View */

$this->title = 'DILG XI - EMPC';
?>
<div class="site-index">


    <div class="box box-primary direct-chat direct-chat-primary">
        <div class="box-header with-border">
            <h3 class="box-title">DILG XI EMPLOYEES MULTI-PURPOSE COOPERATIVE SYSTEM</h3>
        </div>
        <div class="box-body">
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-warning"></i> Reminder </h4>
                Always make sure that the current System Date is updated.
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-aqua"><i class="fa fa-fw fa-calendar-check-o"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">ACTIVE SYSTEM DATE</span>
                            <span class="info-box-number">4/18/2018</span>
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
                            <span class="info-box-text">NEW MEMBERS (2017)</span>
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
                            <h3 class="box-title text-green"> <i class="fa fa-fw fa-download"></i> SYSTEM UPDATES</h3>

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
                                        <a href="javascript:void(0)" class="product-title">Loan Installment
                                            <span class="label label-warning pull-right">Fix</span></a>
                                        <span class="product-description">Loan Installment now has a uniform or consistent monthly amortization.</span>
                                    </div>
                                </li>

                                <li class="item">
                                    <div class="product-img">
                                    
                                    </div>
                                    <div class="product-info">
                                        <a href="javascript:void(0)" class="product-title">Member Information Conflict
                                            <span class="label label-warning pull-right">Fix</span></a>
                                        <span class="product-description"> System now detects whether a new encoded member already exists to avoid data redundancy. </span>
                                    </div>
                                </li>

                                <li class="item">
                                    <div class="product-img">
                                    
                                    </div>
                                    <div class="product-info">
                                        <a href="javascript:void(0)" class="product-title">UI Enhancement
                                            <span class="label label-warning pull-right">Fix</span></a>
                                        <span class="product-description"> Texts were made larger for a better and clearer viewing. </span>
                                    </div>
                                </li>

                                <li class="item">
                                    <div class="product-img">
                                    
                                    </div>
                                    <div class="product-info">
                                        <a href="javascript:void(0)" class="product-title">Loan Products
                                            <span class="label label-success pull-right">New!</span></a>
                                        <span class="product-description">  New Loan Products requested were now included in the system. </span>
                                    </div>
                                </li>

                                <li class="item">
                                    <div class="product-img">
                                    
                                    </div>
                                    <div class="product-info">
                                        <a href="javascript:void(0)" class="product-title">Emailing Facility
                                            <span class="label label-success pull-right">New!</span></a>
                                        <span class="product-description"> The System now a facility that enables the user to email the members when their accounts are about to due. </span>
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
 
