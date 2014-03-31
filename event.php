<?php
/**
 * @author Jeffy Shih <jeffy@ninthday.info>
 * @copyright (c) 2014, Jeffy Shih - http://www.linkedin.com/pub/shiuh-feng-shih/4a/b25/31a
 * 
 * niceTwapperKeeper - Twitter Archiving Application
 * (https://github.com/ninthday/niceKeeper)
 * 
 * This program is from youTwapperKeeper on the Github.
 * (https://github.com/540co/yourTwapperKeeper)
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program;
 */
session_start();

// OAuth login check
if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
    $logged_in = FALSE;
    header('Location: index.php');
    exit();
} else {
    require_once('config.php');
    require_once('function.php');
    require_once('twitteroauth.php');

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
        <title>Event | <?php echo $tk_page_title; ?> - Archive your own tweets</title>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="resources/css/bootstrap-datetimepicker.min.css">
        <link rel="stylesheet" href="css/nicekeeper.css">
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
                        <li><a href="view_saved.php">Saved Archive</a></li>
                        <li class="active"><a href="#">Event</a></li>
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
                <div class="col-md-9">
                    <p><center><a href='index.php'><img src='resources/ownerLogo.png'/></a></center></p>
                </div>
                <div class="col-md-3">
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
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="well">
                        <form class="form-inline" action='create_event.php' method='post' role="form">
                            <div class="form-group">
                                <h4><span class="glyphicon glyphicon-flag"></span>&nbsp;</h4>
                            </div>
                            <div class="form-group">
                                <label class="sr-only" for="evntitle">Event Title</label>
                                <input type="text"  name="evntitle" class="form-control" id="evntitle" placeholder="Event Title">
                            </div>
                            <div class="form-group">
                                <label class="sr-only" for="InputDescription">Description</label>
                                <input type="text" name="description" class="form-control" id="InputDescription" placeholder="Description">
                            </div>
                            <div class="form-group">
                                <label class="sr-only" for="InputTime">Event Time</label>
                                <input type="text" name="evnTime" class="form-control" id="InputTime" placeholder="Event Times" data-date-format="YYYY-MM-DD">
                            </div>
                            <input type='submit' class="btn btn-primary" value ='Add Event'/>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <hr>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="bubble">
                        <div class="bubble-inblock">
                            <h4><span class="glyphicon glyphicon-flag"></span>123
                                <div class="pull-right">
                                    <span class="label label-danger" title="Runing Archives"><span class="glyphicon glyphicon-inbox"></span>&nbsp;2</span>
                                    <span class="label label-success" title="Saved Archives"><span class="glyphicon glyphicon-inbox"></span>&nbsp;4</span>
                                </div>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bubble">
                        <div class="bubble-inblock">
                            <div class="event-head">
                                <h4><span class="glyphicon glyphicon-flag"></span>123</h4>
                            </div>
                            <div class="event-body">
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>
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
        <script src="resources/js/moment.min.js"></script>
        <script src="resources/js/bootstrap-datetimepicker.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $("#InputTime").datetimepicker({
                    pickTime: false
                });
            });
        </script>
    </body>
</html>
