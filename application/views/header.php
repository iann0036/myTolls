<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
    <meta charset="utf-8"/>
    <title>myTolls | Pay tolls and save money on toll roads</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no"/>

    <link rel="apple-touch-icon" sizes="57x57" href="/assets/ico/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/assets/ico/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/assets/ico/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/assets/ico/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/assets/ico/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/assets/ico/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/assets/ico/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/assets/ico/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/ico/apple-touch-icon-180x180.png">
    <link rel="icon" type="image/png" href="/assets/ico/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/assets/ico/android-chrome-192x192.png" sizes="192x192">
    <link rel="icon" type="image/png" href="/assets/ico/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="/assets/ico/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="/manifest.json">
    <link rel="mask-icon" href="/assets/ico/safari-pinned-tab.svg" color="#2e2a2b">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-TileImage" content="/assets/ico/mstile-144x144.png">
    <meta name="theme-color" content="#2e2a2b">

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta content="Save money on toll charges! myTolls provides discounts for outstanding tolls. Now available for Sydney." name="description"/>
    <meta content="myTolls Admin" name="author"/>
    <link href="/assets/plugins/pace/pace-theme-flash.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/plugins/boostrapv3/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/plugins/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/plugins/jquery-scrollbar/jquery.scrollbar.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="/assets/plugins/bootstrap-select2/select2.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="/assets/plugins/switchery/css/switchery.min.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="/assets/plugins/jquery-datatable/media/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/plugins/jquery-datatable/extensions/FixedColumns/css/dataTables.fixedColumns.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/plugins/datatables-responsive/css/datatables.responsive.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="/assets/css/pages-icons.css" rel="stylesheet" type="text/css">
    <link class="main-stylesheet" href="/assets/css/pages.css" rel="stylesheet" type="text/css"/>
    <!--[if lte IE 9]>
    <link href="/assets/plugins/codrops-dialogFx/dialog.ie.css" rel="stylesheet" type="text/css" media="screen" />
    <![endif]-->

    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-50859151-5', 'auto');
        ga('send', 'pageview');

    </script>
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script>
        Number.prototype.formatMoney = function(c, e, d, t){
            var n = this,
                c = isNaN(c = Math.abs(c)) ? 2 : c,
                e = e == undefined ? "" : e,
                d = d == undefined ? "." : d,
                t = t == undefined ? "," : t,
                s = n < 0 ? "-" : "",
                i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
                j = (j = i.length) > 3 ? j % 3 : 0;
            if (isNaN(parseFloat(n)) || !isFinite(n) || n==0)
                return "Unknown";
            return s + "$" + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "") + e;
        };
    </script>
</head>
<body class="fixed-header ">

<nav class="page-sidebar" data-pages="sidebar">

    <div class="sidebar-header">
        <img src="/assets/img/logo_white.png" alt="myTolls Logo" class="brand" data-src="/assets/img/logo_white.png" data-src-retina="/assets/img/logo_white_2x.png" width="78" height="22">
    </div>

    <div class="sidebar-menu">

        <ul class="menu-items">
            <li class="m-t-30">
                <a href="/" class="detailed">
                    <span class="title mytolls-padding-top-4"><b>Dashboard</b></span>
                </a>
                <span class="<?php if ($this->uri->segment(1)=="") echo 'bg-success '; ?>icon-thumbnail"><i class="pg-home"></i></span>
            </li>
            <li class="">
                <a href="/info/" class="detailed">
                    <span class="title mytolls-padding-top-4"><b>Toll Info</b></span>
                </a>
                <span class="<?php if ($this->uri->segment(1)=="info") echo 'bg-success '; ?>icon-thumbnail"><i class="fa fa-info"></i></span>
            </li>
            <li class="" onclick="activateLoadingModal()">
                <a href="/tolls/" class="detailed">
                    <span class="title mytolls-padding-top-4"><b>My Tolls</b></span>
                </a>
                <span class="<?php if ($this->uri->segment(1)=="tolls") echo 'bg-success '; ?>icon-thumbnail"><i class="fa fa-road"></i></span>
            </li>
            <li class="">
                <a href="/notice/" class="detailed">
                    <span class="title mytolls-padding-top-4"><b>Toll Notice</b></span>
                </a>
                <span class="<?php if ($this->uri->segment(1)=="notice") echo 'bg-success '; ?>icon-thumbnail"><i class="pg-form"></i></span>
            </li>
            <li class="">
                <a href="/support/" class="detailed">
                    <span class="title mytolls-padding-top-4"><b>Support</b></span>
                </a>
                <span class="<?php if ($this->uri->segment(1)=="support") echo 'bg-success '; ?>icon-thumbnail"><i class="fa fa-support"></i></span>
            </li>
        </ul>
        <div class="clearfix"></div>
    </div>

</nav>



<div class="page-container ">

    <div class="header ">

        <div class="container-fluid relative">

            <div class="pull-left full-height visible-sm visible-xs">

                <div class="header-inner">
                    <a href="#" class="btn-link toggle-sidebar visible-sm-inline-block visible-xs-inline-block padding-5" data-toggle="sidebar">
                        <span class="icon-set menu-hambuger"></span>
                    </a>
                </div>

            </div>
            <div class="pull-center hidden-md hidden-lg">
                <div class="header-inner">
                    <div class="brand inline">
                        <img src="/assets/img/logo.png" alt="myTolls Logo" data-src="/assets/img/logo.png" data-src-retina="/assets/img/logo_2x.png" width="78" height="22">
                    </div>
                </div>
            </div>
        </div>

        <div class=" pull-left sm-table hidden-xs hidden-sm">
            <div class="header-inner">
                <div class="brand inline">
                    <img src="/assets/img/logo.png" alt="myTolls Logo" data-src="/assets/img/logo.png" data-src-retina="/assets/img/logo_2x.png" width="78" height="22">
                </div>
            </div>
        </div>
        <div class=" pull-right">
            <div class="header-inner">
            </div>
        </div>
        <div class=" pull-right">
        </div>
    </div>


    <div class="page-content-wrapper ">
