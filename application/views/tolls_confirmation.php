<div class="content ">

<div class="jumbotron" data-pages="parallax">
<div class="container-fluid container-fixed-lg sm-p-l-20 sm-p-r-20">
<div class="inner">
 
<ul class="breadcrumb">
<li>
<a href="/">Home</a>
</li>
<li><a href="/tolls/">My Tolls</a>
</li>
<li><a href="/tolls/pay/" class="active">Payment Confirmation</a>
</li>
</ul>
 
<div class="row">
<!--
<div class="col-lg-7 col-md-6 ">
 
<div class="full-height">
<div class="panel-body text-center">
123
</div>
</div>
 
</div>
-->
<div class="col-lg-5 col-md-6 ">
 
<div class="panel panel-transparent">
<div class="panel-heading">
<div class="panel-title">Payment Confirmation
</div>
</div>
</div>
 
</div>
</div>
</div>
</div>
</div>

<div class="container-fluid container-fixed-lg">
    <div class="row">
        <div class="col-md-6 b-r b-dashed b-grey ">
            <div class="panel panel-default">
                <div class="panel-body">
            <?php if (!$declined) { ?>
                <h2>Thank You</h2>
                <p>Your transaction has been processed successfully. Outstanding tolls may take up to 48 hours to appear as paid.</p>
                <p>Your payment receipt number is:</p>
                <h4 class="font-montserrat no-margin text-uppercase"><?php echo $charge_id; ?></h4>
            <?php } else { ?>
                <h2>An Error Occurred</h2>
                <p>Unfortunately, we could not process your transaction due to your card being declined. Please check with the card issuer.</p>
            <?php } ?>
                </div>
            </div>
        </div>
        <?php if (!$declined) { ?>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h5>
                        E-mail Receipt
                    </h5>
                    <?php
                    if ($this->session->flashdata('receipt_email_status')=="success") {
                    ?>
                        <div class="alert alert-success" role="alert">
                            <strong>Success: </strong>The receipt has been sent to the e-mail address requested.
                        </div>
                    <?php
                    } else if ($this->session->flashdata('receipt_email_status')=="error") {
                    ?>
                        <div class="alert alert-danger" role="alert">
                            <strong>Error: </strong>There was an issue sending the receipt to the e-mail address specified.
                        </div>
                    <?php
                    }
                    ?>
                    <p>If you would like a copy of your receipt e-mailed to you, please fill in and submit the below form.</p>
                    <br />
                    <form role="form" action="/tolls/email_receipt/" method="post">
                        <input type="hidden" name="charge_id" value="<?php echo $charge_id; ?>" />
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. you@somesite.com">
                        </div>
                        <br />
                        <input type="submit" class="btn btn-primary btn-cons" value="Send E-mail">
                    </form>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
</div>
