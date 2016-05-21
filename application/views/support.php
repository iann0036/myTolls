<div class="content ">

<div class="jumbotron" data-pages="parallax">
<div class="container-fluid container-fixed-lg sm-p-l-20 sm-p-r-20">
<div class="inner">
 
<ul class="breadcrumb">
<li>
<a href="/">Home</a>
</li>
<li><a href="/support/" class="active">Support</a>
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
<div class="panel-title">Support
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
                    <h4>Frequently Asked Questions</h4>
                    <strong>I have an e-Tag / e-Pass. Can myTolls help me?</strong>
                    <p>If you have a tag, this service can help in other ways such as verification of your trip times but the pricing will not be better than that of the tag.</p>
                    <strong>How long will it take my tolls to be paid?</strong>
                    <p>Tolls are usually paid off within minutes of completion of your purchase, however tolling providers may take up to 3 days to show the tolls as processed.</p>
                    <strong>How can I be notified of new tolls for my vehicle?</strong>
                    <p>Simply visit the dashboard and enter your vehicle registration details, then click the <b>Subscribe to updates</b> button to subscribe to updates with your e-mail address.</p>
                    <strong>When will Sydney Harbour Bridge/Tunnel tolls be searchable?</strong>
                    <p>We have been in contact with the Roads and Maritime Services business team who have indicated they have no plans of exposing any interfaces which would allow us to perform searches without the Toll Notice number. We are currently evaluating other options, however we do not expect this to be a feature in the near future.</p>
                    <strong>What about the M4 Western Motorway?</strong>
                    <p>The M4 is a 40 kilometre motorway which extends from Concord in Sydney's inner west to Lapstone at the foothills of the Blue Mountains. While the M4 Motorway was previously a toll road, on 16 February 2010 the toll was removed and the operation of the road was handed back to the NSW Government. Travel on this motorway is free for all vehicles.</p>
                    <br/>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h4>Contact Us</h4>
                    <?php
                    if ($mailsent) {
                        ?>
                        <div class="alert alert-success" role="alert">
                            <button class="close" data-dismiss="alert"></button>
                            <strong>Success: </strong>Your message has been sent. If you requested a response, you should hear back from us within 24 hours.
                        </div>
                        <?php
                    }
                    ?>
                    <p>Reach out to our support team using the form below if you have any questions or feedback. You can also e-mail us directly at <a href="mailto:support@mytolls.com">support@mytolls.com</a>.</p>
                    <br/>
                    <form class="" role="form" lpformnum="1" action="/support/" method="post">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group form-group-default">
                                    <label>First name</label>
                                    <input type="text" name="fname" class="form-control" required="">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group form-group-default">
                                    <label>Last name</label>
                                    <input type="text" name="lname" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group  form-group-default">
                            <label>E-mail</label>
                            <input type="email" name="email" class="form-control" placeholder="eg. you@example.com" required="">
                        </div>
                        <div class="form-group  form-group-default">
                            <label>Message</label>
                            <textarea style="min-height: 90px;" name="message" class="form-control" rows="5"></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
