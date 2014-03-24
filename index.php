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
                        <li class="active"><a href="#">Runing Archive</a></li>
                        <li><a href="view_saved.php">Saved Archive</a></li>
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
                <?php if ($logged_in) { ?>
                    <div class="well">
                        <form class="form-inline" action='create.php' method='post' role="form">
                            <div class="form-group">
                                <label class="sr-only" for="InputKeyword">Keyword or Hashtag</label>
                                <input type="text"  name="keyword" class="form-control" id="InputKeyword" placeholder="Keyword or Hashtag">
                            </div>
                            <div class="form-group">
                                <label class="sr-only" for="InputDescription">Description</label>
                                <input type="text" name="description" class="form-control" id="InputDescription" placeholder="Description">
                            </div>
                            <div class="form-group">
                                <label class="sr-only" for="InputTags">Tags</label>
                                <input type="text" name="tags" class="form-control" id="InputTags" placeholder="Tags">
                            </div>
                            <input type='submit' class="btn btn-primary" value ='Create Archive'/>
                        </form>
                    </div>
                <?php } ?>
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
                            <th>Archive ID</th><th>Keyword / Hashtag</th><th>Description</th><th>Tags</th><th>Screen Name</th><th>Count</th><th>Create Time</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // list table of archives
                        $archives = $tk->listArchive();
                        foreach ($archives['results'] as $value) {
                            echo "<tr><td>" . $value['id'] . "</td><td>" . $value['keyword'] . "</td><td>" . $value['description'] . "</td><td>" . $value['tags'] . "</td><td>" . $value['screen_name'] . "</td><td>" . $value['count'] . "</td><td>" . $value['create_time'] . "</td>";
                            echo "<td>";
                            echo '<a href="archive.php?id=' . $value['id'] . '" class="btn btn-warning" title="View Archive" target="_blank"><span class="glyphicon glyphicon-th-list"></span></a>';
                            if (isset($_SESSION['access_token']) && ($_SESSION['access_token']['screen_name'] == $value['screen_name'])) {
                                echo '&nbsp;<a href="#" class="btn btn-info" data-toggle="modal" data-target="#edit-arch-' . $value['id'] . '" title="Edit Archive"><span class="glyphicon glyphicon-edit"></span></a>';
                                echo '&nbsp;<a href="#" class="btn btn-danger" data-toggle="modal" data-target="#del-arch-' . $value['id'] . '" title="Delete Archive"><span class="glyphicon glyphicon-trash"></span></a>';
                                echo '&nbsp;<a href="#" class="btn btn-success" data-toggle="modal" data-target="#save-arch-' . $value['id'] . '" title="Stop and Save Archive"><span class="glyphicon glyphicon-import"></span></a>';

                                echo '<!-- Edit Modal -->';
                                echo '<div class="modal fade" id="edit-arch-' . $value['id'] . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">';
                                echo '<div class="modal-dialog"><div class="modal-content">';
                                echo '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
                                echo '<h4 class="modal-title text-info" id="myModalLabel">Edit Archive</h4></div>';
                                echo '<div class="modal-body">';
                                echo '<form class="form-horizontal" method="post" action="update.php" role="form">';
                                echo '<input type="hidden" name="id" value="' . $value['id'] . '"/>';
                                echo '<div class="form-group"><label for="arch-des-' . $value['id'] . '" class="col-sm-2 control-label">Description</label><div class="col-sm-10"><input type="input" name="description" class="form-control" id="arch-des-' . $value['id'] . '" placeholder="Description" value="' . $value['description'] . '"></div></div>';
                                echo '<div class="form-group"><label for="arch-tag-' . $value['id'] . '" class="col-sm-2 control-label">Tags</label><div class="col-sm-10"><input type="input" name="tags"  class="form-control" id="arch-tag-' . $value['id'] . '" placeholder="Tags" value="' . $value['tags'] . '"></div></div>';
                                echo '</div>';
                                echo '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button>&nbsp;<button type="submit" class="btn btn-info">Update changes</button></div>';
                                echo '</form></div></div></div>';

                                echo '<!-- Delete Modal -->';
                                echo '<div class="modal fade" id="del-arch-' . $value['id'] . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">';
                                echo '<div class="modal-dialog"><div class="modal-content">';
                                echo '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title text-danger" id="myModalLabel">Delete Archive</h4></div>';
                                echo '<div class="modal-body">Are you sure you want to delete ' . $value['keyword'] . ' archive?</div>';
                                echo '<div class="modal-footer"><form method="post" action="delete.php"><input type="hidden" name="id" value="' . $value['id'] . '"/><button type="button" class="btn btn-default" data-dismiss="modal">Close</button><button type="submit" class="btn btn-danger">Delete</button></form></div>';
                                echo '</div></div></div>';

                                echo '<!-- Save Modal -->';
                                echo '<div class="modal fade" id="save-arch-' . $value['id'] . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">';
                                echo '<div class="modal-dialog"><div class="modal-content">';
                                echo '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title text-success" id="myModalLabel">Stop and Save Archive</h4></div>';
                                echo '<div class="modal-body">Are you sure you want to <strong>Stop</strong> and <strong>Save</strong> ' . $value['keyword'] . ' archive?</div>';
                                echo '<div class="modal-footer"><form method="post" action="save_archive.php"><input type="hidden" name="id" value="' . $value['id'] . '"/><button type="button" class="btn btn-default" data-dismiss="modal">Close</button><button type="submit" class="btn btn-success">Stop &amp; Save</button></form></div>';
                                echo '</div></div></div>';
                            }

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
