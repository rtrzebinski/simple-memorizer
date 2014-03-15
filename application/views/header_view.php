<!DOCTYPE HTML>
<html>
    <head>
        <!-- metadata -->
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <!-- jQuery -->
        <script src="//code.jquery.com/jquery-1.10.1.min.js"></script>
        <script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>

        <!-- jQuery UI -->
        <script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
        <link href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />

        <!-- jTable -->
        <link href="/static/jtable.2.3.1/themes/metro/blue/jtable.min.css" rel="stylesheet" type="text/css" />
        <script src="/static/jtable.2.3.1/jquery.jtable.js" type="text/javascript"></script>

        <!-- Twitter bootstrap CDN -->
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>

        <!-- CSS -->
        <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
        <link href="/static/css/social-buttons.css" rel="stylesheet" type="text/css" />
        <link href="/static/css/main.css" rel="stylesheet" type="text/css" />
    </head>
    <body class="col-xs-12 col-sm-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">

        <nav class="navbar navbar-default" role="navigation">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/">Simple memorizer</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">


                <?php if ($userId): ?>
                    <ul class="nav navbar-nav navbar-right">
                        <li><a>Hi <?= $userName ?></a></li>
                        <li><a href="/logout">Logout</a></li>
                    </ul>

                <?php else: ?>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Log in <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="/login/oauth/facebook">with facebook</a></li>
                                <li><a href="/login/oauth/google">with google</a></li>
                            </ul>
                        </li>
                    </ul>

                <?php endif; ?>


            </div><!-- /.navbar-collapse -->
        </nav>

        <?php if (!$userId): ?>

            <div class="jumbotron">
                <h2>Welcome to Simple Memorizer!</h2>
                <p>A lightweight web application that helps you memorize anything you need.</p>
                <a href="/login/oauth/facebook" class="btn btn-facebook"><i class="fa fa-facebook"></i> | Connect with Facebook</a>
                <a href="/login/oauth/google" class="btn btn-google-plus"><i class="fa fa-google-plus"></i> | Connect with Google Plus</a>
            </div>

        <?php endif; ?>