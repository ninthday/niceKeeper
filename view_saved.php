<?php
/*
  yourTwapperKeeper - Twitter Archiving Application - http://your.twapperkeeper.com
  Copyright (c) 2010 John O'Brien III - http://www.linkedin.com/in/jobrieniii

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// Set Important / Load important
session_start();
require_once('config.php');
require_once('function.php');
require_once('twitteroauth.php');

// OAuth login check
if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
    $login_status = "<a href='./oauthlogin.php' ><img src='./resources/lighter.png'/></a>";
    $logged_in = FALSE;
} else {
    $access_token = $_SESSION['access_token'];
    $connection = new TwitterOAuth($tk_oauth_consumer_key, $tk_oauth_consumer_secret, $access_token['oauth_token'], $access_token['oauth_token_secret']);
    $login_info = $connection->get('account/verify_credentials');
    $login_status = "<a href='./clearsessions.php'>Hi " . $_SESSION['access_token']['screen_name'] . ", logout</a>";
    $logged_in = TRUE;
}
?>

<!DOCTYPE html>
<html lang="zh-tw">

    <head>
        <title><?php echo $tk_page_title; ?> - Archive your own tweets</title>
        <meta http-equiv="content-type" content="text/html;charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->

    </head>

    <body>
        <!-- Fixed navbar -->
        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">Flood and Fire Keeper</a>
                </div>
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <li><a href="index.php">Runing Archive</a></li>
                        <li class="active"><a href="#">Saved Archive</a></li>
                        <li><a href="#contact">Event</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="#">Action</a></li>
                                <li><a href="#">Another action</a></li>
                                <li><a href="#">Something else here</a></li>
                                <li class="divider"></li>
                                <li class="dropdown-header">Nav header</li>
                                <li><a href="#">Separated link</a></li>
                                <li><a href="#">One more separated link</a></li>
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li><?php echo $login_status; ?></li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </div>

        <div class="container" style="margin-top: 60px;" role="main">
            <div class="row">
                <div class="col-xs-9">
                    <p><center><a href='index.php'><img src='resources/ownerLogo.png'/></a></center></p>
                </div>
                <div class="col-xs-3">
                    <?php
                    $archiving_status = $tk->statusArchiving($archive_process_array);
                    $stat_color = ($archiving_status[0]) ? 'success' : 'danger';
                    ?>
                    <div class="panel panel-<?php echo $stat_color; ?>">
                        <div class="panel-heading">Processes State<span class="label label-success pull-right"><?php echo count($archiving_status[1]); ?></span></div>
                        <div class="panel-body">
                            <?php
                            echo '<p class="text-' . $stat_color . '">' . $archiving_status[2] . '</p>';
                            if (isset($_SESSION['access_token']) && in_array($_SESSION['access_token']['screen_name'], $admin_screen_name)) {
                                if ($archiving_status[0] == FALSE) {
                                    echo '<a href="startarchiving.php" class="btn btn-success btn-sm" title="Start Archving"><span class="glyphicon glyphicon-play"></span> Start</a>';
                                } else {
                                    echo '<a href="stoparchiving.php" class="btn btn-danger btn-sm" title="Stop Archiving"><span class="glyphicon glyphicon-stop"></span> Stop</a>';
                                }
                            }
                            ?>
                        </div>
                        <?php
                        if ($logged_in && count($archiving_status[1] > 0)) {
                            echo '<ul class="list-group">';
                            foreach ($archiving_status[1] as $rd_pid) {
                                echo '<li class="list-group-item"><span class="glyphicon glyphicon-tasks"></span> System Process ID: ' . $rd_pid . '</li>';
                            }
                            echo '</ul>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <hr> 
                <?php if (isset($_SESSION['notice'])) { ?>
                    <div class="alert alert-warning alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <span class="glyphicon glyphicon-info-sign"></span>&nbsp;<?php echo $_SESSION['notice']; ?>
                    </div>
                    <?php
                    unset($_SESSION['notice']);
                }
                ?> 
            </div>
            <div class="row">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Archive ID</th><th>Keyword / Hashtag</th><th>Description</th><th>Tags</th><th>Screen Name</th><th>Count</th><th>Create Time</th><th>Saved Time</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // list table of archives
                        $archives = $tk->listSavedArchive();
                        foreach ($archives['results'] as $value) {
                            echo "<tr><td>" . $value['id'] . "</td><td>" . $value['keyword'] . "</td><td>" . $value['description'] . "</td><td>" . $value['tags'] . "</td><td>" . $value['screen_name'] . "</td><td>" . $value['count'] . "</td><td>" . $value['create_time'] . "</td><td>" . $value['save_time'] . "</td>";
                            echo "<td>";
                            echo '<a href="archive.php?id=' . $value['id'] . '" class="btn btn-warning" title="View Archive" target="_blank"><span class="glyphicon glyphicon-th-list"></span></a>';
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="row">
                <hr>
            </div>
            <?php
            include_once './footer.php';
            ?>
        </div>
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    </body>
</html>
