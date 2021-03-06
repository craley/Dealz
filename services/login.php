<!DOCTYPE html>
<!--
php template for login screen.
-->
<html>
    <head>
        <title>Deals</title>
        <meta charset="UTF-8">
        <link rel="shortcut icon" href="https://spinspire.com/sites/spinspire.com/themes/spinstrap/favicon.ico" type="image/vnd.microsoft.icon">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="js/libs/bootstrap/css/bootstrap.css">
        <link rel="stylesheet" href="js/libs/bootstrap/css/bootstrap-theme.css">
        <link rel="stylesheet" href="css/styles.css">
        <script src="js/libs/jquery/jquery.js"></script>
        <script src="js/libs/bootstrap/js/bootstrap.js"></script>
        <script src="https://apis.google.com/js/client:platform.js?onload=start" async defer></script>
    </head>
    <body>
        <nav role="navigation" class="navbar navbar-default navbar-fixed-top" id="topbar">
            <div class="container">
                <a class="navbar-brand" href="#">Dealz</a>
                <ul class="nav navbar-nav mini" id="chooser">
                    <li><a href="#">Home</a></li>
                </ul>
            </div>
        </nav>
        <div class="container" id="main">
            <div id="myCarousel" class="carousel slide" data-ride="carousel" data-interval="false">
                <!-- Indicators -->
                <ol class="carousel-indicators">
                    <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                    <li data-target="#myCarousel" data-slide-to="1"></li>
                </ol>

                <!-- Wrapper for slides -->
                <div class="carousel-inner" role="listbox">
                    <div class="item active">
                        <div class="panel panel-default panel-primary">
                            <div class="panel-heading text-center">Login<span class="pull-right">Register  <span class="glyphicon glyphicon-arrow-right"></span></span></div>
                            <div class="panel-body">
                                <div class="well">
                                    <form>
                                        <div class="form-group">
                                            <label for="userLogin">Username</label>
                                            <input type="text" class="form-control" id="userLogin" placeholder="Username" autofocus>
                                        </div>
                                        <div class="form-group">
                                            <label for="pswdLogin">Password</label>
                                            <input type="password" class="form-control" id="pswdLogin" placeholder="Password">
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox"> Remember me</label>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="fireLogin">Login</button>
                                        <span id="errmsg"></span>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="item">
                        <div class="panel panel-default panel-primary">
                            <div class="panel-heading text-center">Register<span class="pull-right">Back  <span class="glyphicon glyphicon-arrow-right"></span></span></div>
                            <div class="panel-body">
                                <div class="well">
                                    <form>
                                        <div class="form-group">
                                            <label for="userRegister">Username</label>
                                            <input type="text" class="form-control" id="userRegister" placeholder="Username">
                                        </div>
                                        <div class="form-group">
                                            <label for="pswdRegister">Password</label>
                                            <input type="password" class="form-control" id="pswdRegister" placeholder="Password">
                                        </div>
                                        <div class="form-group">
                                            <label for="emailRegister">Email</label>
                                            <input type="email" class="form-control" id="emailRegister" placeholder="Email">
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox"> Remember me</label>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="fireRegister">Register</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Left and right controls -->
                <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
                    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
                    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>

        </div>

        <!-- Begin vendor login -->
        <nav role="navigation" class="navbar navbar-default navbar-fixed-bottom" id="bottombar">
            <div class="container">
                <ul class="nav nav-pills" id="bottomload">
                    <li><a href="#con2"><span class="glyphicon glyphicon-home"></span> Google+</a></li>
                    <li><a href="#con2">Facebook</a></li>
                    <li><a href="#con2">Twitter</a></li>
                    <li>
                        <div id="signinButton">
                            <span class="g-signin"
                                  data-scope="https://www.googleapis.com/auth/plus.login"
                                  data-clientid="961741834099-nv3c7j13nm3fmis23sm1g8g83ctr995l.apps.googleusercontent.com"
                                  data-redirecturi="postmessage"
                                  data-accesstype="offline"
                                  data-cookiepolicy="single_host_origin"
                                  data-callback="signInCallback">

                            </span>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <script src="js/app.js"></script>
    </body>
</html>
<?php

