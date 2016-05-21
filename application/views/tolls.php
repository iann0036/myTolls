
<div class="content ">

<div class="jumbotron" data-pages="parallax">
<div class="container-fluid container-fixed-lg sm-p-l-20 sm-p-r-20">
<div class="inner">
 
<ul class="breadcrumb">
<li>
<a href="/">Home</a>
</li>
<li><a href="/tolls/" class="active">Tolls</a>
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
<div class="panel-title">Tolls
</div>
</div>
<div class="panel-body">
<h3>Vehicle Plate Number: <strong><?php echo $plate; ?></strong></h3>
<p><?php echo $rego_details[0]; ?></p>
<p class="small hint-text m-t-5"><?php echo $rego_details[1]; ?></p>
<br>
<?php if ($toll_total==0.33) {
    ?>
    <button style="margin-top: 8px;" class="btn btn-info btn-cons"><b>No outstanding tolls</b></button>
    <?php
} elseif ($rego_details[2]) {
    ?>
    <button style="margin-top: 8px;" class="btn btn-info btn-cons"><b>Tolls Currently Processing</b></button>
    <?php
} else {
    ?>
    <a href="/tolls/pay"><button style="margin-top: 8px;" class="btn btn-primary btn-cons"><b>Pay all unpaid tolls (<?php echo "$".number_format($toll_total,2); ?>)</b></button></a>
    <?php
}
    ?>
&nbsp;<button style="margin-top: 8px;" class="btn btn-complete btn-cons" data-toggle="modal" data-target="#modalSubscribe"><b>Subscribe to updates</b></button>
</div>
</div>
 
</div>
</div>
</div>
</div>
</div>

<div class="container-fluid container-fixed-lg">
<div class="row">
<div class="col-md-12">
 
<div class="panel panel-transparent">
<div class="panel-heading">
<div class="panel-title">My Tolls In Detail
</div>
</div>
<div class="panel-body">
<?php if ($isBike) { ?>
    <div class="alert alert-info" role="alert">
        <strong>Notice: </strong>This vehicle has been detected as a motorbike. If this is incorrect, please <a href="/support/">contact support</a>.
    </div>
<?php } ?>
<?php if ($rego_details[2]) { ?>
    <div class="alert alert-warning" role="alert">
        <strong>Notice: </strong>Toll processing is currently occuring for this vehicle. This process may take up to 3 days. For more information, please <a href="/support/">contact support</a>.
    </div>
<?php } ?>
<?php if ($this->session->flashdata('item')) { ?>
    <div class="alert alert-warning" role="alert">
        <strong>Notice: </strong>Could not talk to the M5 Motorway systems provider. Please try again or <a href="/support/">contact support</a> if issues persist.
    </div>
<?php } ?>
<div id="fullScale" class="table-responsive">
<table class="table table-hover table-condensed table-detailed" id="detailedTable">
<thead>
<tr>
<th style="width:25%">When</th>
<th style="width:25%">Toll Road</th>
<th style="width:25%">Total Price</th>
<th style="width:25%">Status</th>
<th style="display: none;">ID</th>
<th style="display: none;">Admin Charge</th>
<th style="display: none;">Discounted Admin Charge</th>
<th style="display: none;">Toll Charge</th>
<th style="display: none;">Number of Trips</th>
<th style="display: none;">Date / Time</th>
<th style="display: none;">Timestamp</th>
</tr>
</thead>
<tbody>
<?php
$id = 0;
foreach ($tollcheck as $toll_entry) {
    ?>
    <tr>
        <td class="v-align-middle semi-bold"><?php echo $toll_entry['time']; ?></td>
        <td class="v-align-middle"><?php echo $toll_entry['motorway']; ?></td>
        <td class="v-align-middle semi-bold"><?php if (is_numeric($toll_entry['discounted_total_price'])) echo "$".number_format($toll_entry['discounted_total_price'],2)." AUD"; else echo "Unknown"; ?></td>
        <td class="v-align-middle"><?php echo $toll_entry['status']; ?></td>
        <td style="display: none;"><?php echo $id++; ?></td>
        <td style="display: none;"><?php echo $toll_entry['admin_charge']; ?></td>
        <td style="display: none;"><?php echo $toll_entry['discounted_admin_charge']; ?></td>
        <td style="display: none;"><?php echo $toll_entry['toll_charge']; ?></td>
        <td style="display: none;"><?php echo $toll_entry['num_trips']; ?></td>
        <td style="display: none;"><?php echo $toll_entry['datetime']; ?></td>
        <td style="display: none;"><?php echo $toll_entry['timestamp']; ?></td>
    </tr>
    <?php
}
?>
</tbody>
</table>
</div>
    <div id="mobileScale" class="auto-overflow widget-11-2-table" style="display: none;">
        <?php if (count($tollcheck)<1) { ?>
            <h3>No toll data available.</h3>
        <?php } else { ?>
        <table class="table table-condensed table-hover">
            <tbody>
            <?php
            foreach ($tollcheck as $toll_entry) {
                ?>
                <tr>
                    <td class="font-montserrat all-caps fs-12 col-xs-8"><?php echo $toll_entry['motorway']; if ($toll_entry['status']=="Paid") { echo " (Paid)"; } ?></td>
                    <td class="col-xs-4" style="overflow: visible;">
                        <span class="font-montserrat fs-18"><?php if (is_numeric($toll_entry['discounted_total_price'])) echo "$".number_format($toll_entry['discounted_total_price'],2)." AUD"; else echo "Unknown"; ?></span>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php } ?>
    </div>
    <br />
    <p><b>Note:</b> Sydney Harbour Bridge/Tunnel tolls are not searchable, however if you have received a toll notice you can <a href="/notice/">pay for the notice</a>.</p>
</div>
</div>
 
</div>
</div>
</div>
 
</div>

<div class="modal fade fill-in" id="modalSubscribe" tabindex="-1" role="dialog" aria-labelledby="modalSubscribeLabel" aria-hidden="true">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
        <i class="pg-close"></i>
    </button>
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="text-left p-b-5">Subscribe to updates for <span class="semi-bold"><?php echo $plate; ?></span></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form action="/tolls/subscribe/" method="post">
                        <div class="col-md-9" style="padding-top: 8px;">
                            <input type="text" placeholder="Your email address here" class="form-control input-lg" id="email" name="email">
                        </div>
                        <div class="col-md-3 text-center" style="padding-top: 8px;">
                            <button type="submit" class="btn btn-primary btn-lg btn-large fs-15"><b>Subscribe</b></button>
                        </div>
                    </form>
                </div>
                <p class="text-center hinted-text p-t-10 p-r-10" style="padding-top: 4px;">You will receive an update within an hour of your toll being registered on motorway systems</p>
            </div>
            <div class="modal-footer">

            </div>
        </div>
    </div>
</div>

<script>
function onResize() {
    var win = $(window);
    if (win.width() < 529) {
        $('#fullScale').attr('style','display: none;');
        $('#mobileScale').attr('style','display: block;');
    } else {
        $('#fullScale').attr('style','display: block;');
        $('#mobileScale').attr('style','display: none;');
    }
}

window.onload = function() {
    (function ($) {

        'use strict';

        // Initialize a dataTable with collapsible rows to show more details
        var initDetailedViewTable = function () {

            var _format = function (d) {
                var returnstr = '<table class="table table-inline">' +
                    '<tr>' +
                    '<td>When</td>' +
                    '<td>' + d[9] + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td>Administration Charge</td>' +
                    '<td>' + Number(d[5]).formatMoney(2," AUD") + '</td>' +
                    '</tr>';
                if (d[3]=="Unpaid") {
                    returnstr += '<tr>';
                    var discount_or_fee = Number(Number(d[6])-Number(d[5]));
                    if (discount_or_fee>0) {
                        returnstr += '<td>myTolls Fee</td>';
                        returnstr += '<td>' + Number(d[6]-d[5]).formatMoney(2, " AUD") + '</td>';
                    } else {
                        returnstr += '<td>myTolls Discount</td>';
                        returnstr += '<td>-' + Number(d[5]-d[6]).formatMoney(2, " AUD") + '</td>';
                    }
                    returnstr += '</tr>';
                }
                returnstr += '<tr>' +
                '<td>Toll Charge</td>' +
                '<td>' + Number(d[7]).formatMoney(2," AUD") + '</td>' +
                '</tr>' +
                '<tr>' +
                '<td>Number of Trips</td>' +
                '<td>' + d[8] + '</td>' +
                '</tr>' +
                '</table>';

                return returnstr;
            }


            var table = $('#detailedTable');

            table.DataTable({
                "sDom": "t",
                "scrollCollapse": true,
                "paging": false,
                "bSort": false
            });

            // Add event listener for opening and closing details
            $('#detailedTable tbody').on('click', 'tr', function () {
                //var row = $(this).parent()
                if ($(this).hasClass('shown') && $(this).next().hasClass('row-details')) {
                    $(this).removeClass('shown');
                    $(this).next().remove();
                    return;
                }
                var tr = $(this).closest('tr');
                var row = table.DataTable().row(tr);

                $(this).parents('tbody').find('.shown').removeClass('shown');
                $(this).parents('tbody').find('.row-details').remove();

                row.child(_format(row.data())).show();
                tr.addClass('shown');
                tr.next().addClass('row-details');
            });

        }

        initDetailedViewTable();

        $(window).on('resize', function(){
            onResize();
        });
        onResize();

    })(window.jQuery);
}
</script>