
<div class="content ">

    <div class="jumbotron" data-pages="parallax">
        <div class="container-fluid container-fixed-lg sm-p-l-20 sm-p-r-20">
            <div class="inner">

                <ul class="breadcrumb">
                    <li>
                        <a href="/">Home</a>
                    </li>
                    <li><a href="/notice/">Toll Notice</a>
                    </li>
                    <li><a href="/notice/pay/" class="active">Pay Toll Notice</a>
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
                                <div class="panel-title">Pay Toll Notice
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
                <div class="padding-30">
                    <h2>Toll Notice Being Paid</h2>
                    <p>Below is the information regarding the toll notice to be paid today for the vehicle <b><?php echo $plate; ?></b></p>
                    <p class="small hint-text">Please check over this carefully, as payment is final</p>
                    <table class="table table-condensed">
                        <?php
                        $toll_entry = $tollcheck[0];
                        echo '<tr><td class=" col-md-9"><span class="m-l-10 font-montserrat fs-18 all-caps">';
                        echo $toll_entry['motorway'];
                        echo '</span>';
                        if (strpos($toll_entry['time'],"Unknown")===false) {
                            echo '<br /><i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Travelled on ';
                            echo $toll_entry['time'];
                        }
                        echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Toll Charge ($';
                        echo number_format($toll_entry['toll_charge'],2);
                        echo ')<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin Fee ($';
                        echo number_format($toll_entry['admin_charge'],2);
                        echo ')</i></td><td class=" col-md-3 text-right"><span><b>';
                        echo "$".number_format($toll_entry['total_price'],2);
                        echo '</b></span></td></tr>';
                        ?>
                        <tr>
                            <?php
                            if ($discount<0) {
                                ?>
                                <td class=" col-md-9"><span class="m-l-10 font-montserrat fs-18 all-caps">myTolls Fee</span></td>
                                <td class=" col-md-3 text-right"><span><b><?php echo "$".number_format($discount*-1, 2); ?></b></span></td>
                                <?php
                            } else {
                                ?>
                                <td class=" col-md-9"><span class="m-l-10 font-montserrat fs-18 all-caps">myTolls Discount</span></td>
                                <td class=" col-md-3 text-right"><span><b><?php echo "-$" . number_format($discount, 2); ?></b></span></td>
                                <?php
                            }
                            ?>
                        </tr>
                        <tr>
                            <td class=" col-md-9"><span class="m-l-10 font-montserrat fs-18 all-caps">Transaction Fee</span></td>
                            <td class=" col-md-3 text-right"><span><b><?php echo "$".number_format($service_fee,2); ?></b></span></td></tr>
                        </tr>
                        <tr>
                            <td colspan="2" class=" col-md-3 text-right">
                                <h4 class="text-primary no-margin font-montserrat"><?php echo "$".number_format($payment_total,2); ?></h4>
                            </td>
                        </tr>
                    </table>
                    <p class="small">By proceeding, you agree to the service <a href="/terms/">Terms of use</a></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="padding-30">
                    <ul class="list-unstyled list-inline m-l-30">
                        <li><a href="#" class="p-r-30 text-black">Credit Card</a></li>
                        <li><a href="#" onclick="alert('PayPal is not yet supported. Please contact support for further information.');" class="p-r-30 text-black  hint-text">PayPal</a></li>
                    </ul>
                    <form role="form" action="/notice/confirmation/" method="POST" id="payment-form">
                        <input type="hidden" name="idem" value="<?php echo uniqid('tolls_idem_'); ?>" />
                        <div class="bg-master-light padding-30 b-rad-lg">
                            <h2 class="pull-left no-margin">Credit Card</h2>
                            <ul class="list-unstyled pull-right list-inline no-margin">
                                <li>
                                    <a href="#">
                                        <img width="51" height="32" data-src-retina="/assets/img/form-wizard/visa2x.png" data-src="/assets/img/form-wizard/visa.png" class="brand" alt="logo" src="/assets/img/form-wizard/visa.png">
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="hint-text">
                                        <img width="51" height="32" data-src-retina="/assets/img/form-wizard/amex2x.png" data-src="/assets/img/form-wizard/amex.png" class="brand" alt="logo" src="/assets/img/form-wizard/amex.png">
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="hint-text">
                                        <img width="51" height="32" data-src-retina="/assets/img/form-wizard/mastercard2x.png" data-src="/assets/img/form-wizard/mastercard.png" class="brand" alt="logo" src="/assets/img/form-wizard/mastercard.png">
                                    </a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                            <span class='text-danger payment-errors'></span>
                            <div class="form-group form-group-default required m-t-25">
                                <label>Card holder's name</label>
                                <input data-stripe="name" type="text" class="form-control" placeholder="Name on the card">
                            </div>
                            <div class="form-group form-group-default required">
                                <label>Card number</label>
                                <input data-stripe="number" type="text" class="form-control" placeholder="8888-8888-8888-8888">
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Expiration</label>
                                    <br>
                                    <select data-stripe="exp-month" class="cs-select cs-skin-slide" data-init-plugin="cs-select">
                                        <option selected value="01">Jan (01)</option>
                                        <option value="02">Feb (02)</option>
                                        <option value="03">Mar (03)</option>
                                        <option value="04">Apr (04)</option>
                                        <option value="05">May (05)</option>
                                        <option value="06">Jun (06)</option>
                                        <option value="07">Jul (07)</option>
                                        <option value="08">Aug (08)</option>
                                        <option value="09">Sep (09)</option>
                                        <option value="10">Oct (10)</option>
                                        <option value="11">Nov (11)</option>
                                        <option value="12">Dec (12)</option>
                                    </select>
                                    <select data-stripe="exp-year" class="cs-select cs-skin-slide" data-init-plugin="cs-select">
                                        <option selected="selected" value="2016">2016</option>
                                        <option value="2017">2017</option>
                                        <option value="2018">2018</option>
                                        <option value="2019">2019</option>
                                        <option value="2020">2020</option>
                                        <option value="2021">2021</option>
                                        <option value="2022">2022</option>
                                        <option value="2023">2023</option>
                                        <option value="2024">2024</option>
                                        <option value="2025">2025</option>
                                        <option value="2026">2026</option>
                                        <option value="2027">2027</option>
                                        <option value="2028">2028</option>
                                        <option value="2029">2029</option>
                                        <option value="2030">2030</option>
                                    </select>
                                </div>
                                <div class="col-md-2 col-md-offset-4">
                                    <div class="form-group required">
                                        <label>CVC Code</label>
                                        <input data-stripe="cvc" type="text" class="form-control" placeholder="000" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br />
                        <button class="btn btn-primary btn-cons pull-right" type="submit">Pay Now</button>
                        <br />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    <?php
    if (ENVIRONMENT==='production')
        echo "Stripe.setPublishableKey('pk_live_9npHsfBM8sy3tMr5QbBMKiiF');";
    else
        echo "Stripe.setPublishableKey('pk_test_C1m24mjYflI0qG6Z7TlG5x8h');";
    ?>

    var stripeResponseHandler = function(status, response) {
        var $form = $('#payment-form');

        if (response.error) {
            $form.find('.payment-errors').text(response.error.message);
            $form.find('button').prop('disabled', false);
            deactivateLoadingModal();
        } else {
            var token = response.id;
            $form.append($('<input type="hidden" name="stripeToken" />').val(token));
            $form.get(0).submit();
        }
    };

    window.onload = function() {
        $('#payment-form').submit(function(e) {
            var $form = $(this);
            $form.find('button').prop('disabled', true);

            Stripe.card.createToken($form, stripeResponseHandler);

            activateLoadingModal();

            return false;
        });
    };
</script>
