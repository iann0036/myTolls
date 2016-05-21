
<div class="content ">

    <div class="jumbotron" data-pages="parallax">
        <div class="container-fluid container-fixed-lg sm-p-l-20 sm-p-r-20">
            <div class="inner">

                <ul class="breadcrumb">
                    <li>
                        <a class="active" href="/">Home</a>
                    </li>
                </ul>

                <div class="row">

                    <div class="col-lg-5 col-md-6 ">

                        <div class="panel panel-transparent">
                            <div class="panel-heading">
                                <div class="panel-title">Getting started
                                </div>
                            </div>
                            <div class="panel-body">
                                <h3>Simply enter your vehicle plate number in the box below, select your state and hit <b>Search</b>.</h3>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid container-fixed-lg">
        <div class="row">
            <div class="col-sm-12 col-md-6">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel-title">Search Tolls
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <h5>Enter your vehicle details below
                                </h5>
                                <p>&nbsp;</p>
                                <form role="form" action="/tolls/" method="post">
                                    <div class="form-group form-group-default">
                                        <label class="label-lg">Vehicle Plate Number</label>
                                        <input name="plate" type="text" placeholder="ABC123" class="form-control input-lg">
                                    </div>
                                    <br/>
                                    <div class="form-group btn-group" data-toggle="buttons">
                                        <label class="btn btn-default active" style="margin-bottom: 4px;">
                                            <input type="radio" name="state" value="nsw" checked=""> <span class="fs-16">NSW</span>
                                        </label>
                                        <label class="btn btn-default" style="margin-bottom: 4px;">
                                            <input type="radio" name="state" value="vic"> <span class="fs-16">VIC</span>
                                        </label>
                                        <label class="btn btn-default" style="margin-bottom: 4px;">
                                            <input type="radio" name="state" value="qld"> <span class="fs-16">QLD</span>
                                        </label>
                                        <label class="btn btn-default" style="margin-bottom: 4px;">
                                            <input type="radio" name="state" value="act"> <span class="fs-16">ACT</span>
                                        </label>
                                        <label class="btn btn-default" style="margin-bottom: 4px;">
                                            <input type="radio" name="state" value="sa"> <span class="fs-16">SA</span>
                                        </label>
                                        <label class="btn btn-default" style="margin-bottom: 4px;">
                                            <input type="radio" name="state" value="nt"> <span class="fs-16">NT</span>
                                        </label>
                                        <label class="btn btn-default" style="margin-bottom: 4px;">
                                            <input type="radio" name="state" value="wa"> <span class="fs-16">WA</span>
                                        </label>
                                        <label class="btn btn-default" style="margin-bottom: 4px;">
                                            <input type="radio" name="state" value="tas"> <span class="fs-16">TAS</span>
                                        </label>
                                    </div>
                                    <br/>
                                    <br/>
                                    <div class="form-group">
                                        <button data-toggle="modal" data-target="#loadingModal" class="btn btn-lg btn-primary btn-cons" type="submit"><b>Search</b></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-sm-12 col-md-6">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel-title">Supported Toll Roads
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <ul>
                                <li>Hills M2</li>
                                <li>M5 South-West Motorway</li>
                                <li>Westlink M7</li>
                                <li>Cross City Tunnel</li>
                                <li>Eastern Distributor</li>
                                <li>Lane Cove Tunnel</li>
                                <li>Military Roam E-ramp</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>