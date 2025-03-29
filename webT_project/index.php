<?php
session_start();

$host = '127.0.0.1'; // Database host
$db = 'picogram'; // Database name
$user = 'root'; // Database username
$pass = ''; // Database password

// Create connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    if (isset($_SESSION['user_id'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        
        // Check if the file already exists in the database
        $stmt = $conn->prepare("SELECT * FROM pictures WHERE user_id = ? AND image_path = ?");
        $stmt->bind_param("is", $_SESSION['user_id'], $target_file);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('You have already uploaded this picture.');</script>";
        } else {
            // Move the uploaded file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Insert into database
                $stmt = $conn->prepare("INSERT INTO pictures (user_id, image_path) VALUES (?, ?)");
                $stmt->bind_param("is", $_SESSION['user_id'], $target_file);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "<script>alert('Failed to move uploaded file.');</script>";
            }
        }
    } else {
        echo "<script>alert('You must be logged in to upload a picture.');</script>";
    }
}
// Fetch pictures
$pictures = $conn->query("SELECT pictures.*, users.username FROM pictures JOIN users ON pictures.user_id = users.id");

// Fetch most liked picture
$most_liked = $conn->query("SELECT * FROM pictures ORDER BY likes DESC LIMIT 1");

// Handle like/dislike
if (isset($_POST['like']) || isset($_POST['dislike'])) {
    if (isset($_SESSION['user_id'])) {
        $picture_id = $_POST['picture_id'];
        $user_id = $_SESSION['user_id'];

        if (isset($_POST['like'])) {
            // Check if user already liked
            $check_like = $conn->query("SELECT * FROM likes WHERE user_id = $user_id AND picture_id = $picture_id");
            if ($check_like->num_rows == 0) {
                // User likes the picture
                $conn->query("INSERT INTO likes (user_id, picture_id) VALUES ($user_id, $picture_id)");
                $conn->query("UPDATE pictures SET likes = likes + 1 WHERE id = $picture_id");
            } else {
                // User already liked, do nothing or show a message
                echo "<script>alert('You have already liked this picture.');</script>";
            }
        } elseif (isset($_POST['dislike'])) {
            // Check if user already liked
            $check_like = $conn->query("SELECT * FROM likes WHERE user_id = $user_id AND picture_id = $picture_id");
            if ($check_like->num_rows > 0) {
                // User is switching from like to dislike
                $conn->query("DELETE FROM likes WHERE user_id = $user_id AND picture_id = $picture_id");
                $conn->query("UPDATE pictures SET likes = likes - 1 WHERE id = $picture_id");
                
                // Now insert dislike
                $check_dislike = $conn->query("SELECT * FROM dislikes WHERE user_id = $user_id AND picture_id = $picture_id");
                if ($check_dislike->num_rows == 0) {
                    // User dislikes the picture
                    $conn->query("INSERT INTO dislikes (user_id, picture_id) VALUES ($user_id, $picture_id)");
                    $conn->query("UPDATE pictures SET dislikes = dislikes + 1 WHERE id = $picture_id");
                } else {
                    // User already disliked, do nothing or show a message
                    echo "<script>alert('You have already disliked this picture.');</script>";
                }
            } else {
                // User has not liked the picture, check if they already disliked
                $check_dislike = $conn->query("SELECT * FROM dislikes WHERE user_id = $user_id AND picture_id = $picture_id");
                if ($check_dislike->num_rows == 0) {
                    // User dislikes the picture
                    $conn->query("INSERT INTO dislikes (user_id, picture_id) VALUES ($user_id, $picture_id)");
                    $conn->query("UPDATE pictures SET dislikes = dislikes + 1 WHERE id = $picture_id");
                } else {
                    // User already disliked, do nothing or show a message
                    echo "<script>alert('You have already disliked this picture.');</script>";
                }
            }
        }
    } else {
        echo "<script>alert('You must be logged in to like or dislike a picture.'); $('#loginModal').modal('show');</script>";
    }
}/* elseif (isset($_POST['dislike'])) {
            // Check if user already liked
            $check_like = $conn->query("SELECT * FROM likes WHERE user_id = $user_id AND picture_id = $picture_id");
            if ($check_like->num_rows > 0) {
                // User is switching from like to dislike
                $conn->query("DELETE FROM likes WHERE user_id = $user_id AND picture_id = $picture_id");
                $conn->query("UPDATE pictures SET likes = likes - 1 WHERE id = $picture_id");
            }
            // Now check if user already disliked
            $check_dislike = $conn->query("SELECT * FROM dislikes WHERE user_id = $user_id AND picture_id = $picture_id");
            if ($check_dislike->num_rows == 0) {
                // User dislikes the picture
                $conn->query("INSERT INTO dislikes (user_id, picture_id) VALUES ($user_id, $picture_id)");
                $conn->query("UPDATE pictures SET dislikes = dislikes + 1 WHERE id = $picture_id");
            } else {
                // User already disliked, do nothing or show a message
                echo "<script>alert('You have already disliked this picture.');</script>";
            }
        }
    } else {
        echo "<script>alert('You must be logged in to like or dislike a picture.'); $('#loginModal').modal('show');</script>";
    }
}-->*/

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    if (isset($_SESSION['user_id'])) {
        $picture_id = $_POST['picture_id'];
        $comment = $_POST['comment_text'];

        $stmt = $conn->prepare("INSERT INTO comments (picture_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $picture_id, $_SESSION['user_id'], $comment);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "<script>alert('You must be logged in to comment.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header with Navbar</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
    /* Your existing styles */
    body {
        background-color: #F8E7F6; /* Set the background color */
    }
    .content-img {
        width: 100%;
        height: auto;
    }
    .card {
        margin-bottom: 20px;
    }
    .logo {
        color: #143D60;
        margin-left: 10px;
        margin-top: 10px;
    }
    .header {
        background-color: #A0C878;
    }
    .most-liked-card {
        margin-bottom: 20px; /* Space below the most liked card */
    }
    .like-dislike {
        margin-right: 10px; /* Space between like and dislike buttons */
        margin-top: 5px; 
        margin-right: 10px; /* Space between like and dislike buttons */
    }
</style>
</head>
<body>

<header class="header">
    <div class="logo"><h3>picOgram</h3></div>
    <nav class="navbar navbar-expand">
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-toggle="modal" data-target="#uploadModal">Upload</a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="signup.php">Signup</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.html">Login</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <img src="https://via.placeholder.com/40" alt="Profile" class="profile-pic">
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header>

<main class="container-fluid mt-4">
    <div class="row">
        <!-- Left Part -->
        <div class="col-md-3 left-part">
            <h2>Most Liked Content</h2>
            <?php if ($most_liked->num_rows > 0): ?>
                <?php $row = $most_liked->fetch_assoc(); ?>
                <div class="card">
                    <div class="card-body text-center">
                        <!-- <h5><?php echo htmlspecialchars($row['username']); ?></h5> -->
                        <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Most Liked" class="content-img">
                        <div>
                            <form method="POST">
                                <input type="hidden" name="picture_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="like" class="like-dislike">👍 <span class="like-count"><?php echo $row['likes']; ?></span></button>
                                <button type="submit" name="dislike" class="like-dislike">👎 <span class="like-count"><?php echo $row['dislikes']; ?></span></button>
                            </form>
                        </div>
                        <form method="POST" class="mt-2">
                            <input type="hidden" name="picture_id" value="<?php echo $row['id']; ?>">
                            <input type="text" name="comment_text" placeholder="Add a comment..." required>
                            <button type="submit" name="comment" class="btn btn-secondary">Comment</button>
                        </form>
                        <div class="comments mt-2">
                            <h6>Comments:</h6>
                            <?php
                            $comments = $conn->query("SELECT comments.comment, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.picture_id = " . $row['id']);
                            while ($comment_row = $comments->fetch_assoc()): ?>
                                <p><strong><?php echo htmlspecialchars($comment_row['username']); ?>:</strong> <?php echo htmlspecialchars($comment_row['comment']); ?></p>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p>No pictures found.</p>
            <?php endif; ?>
        </div>

        <!-- Right Part -->
        <div class="col-md-9 right-part">
            <h2>Gallery</h2>
            <div class="row">
                <?php while ($row = $pictures->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5><?php echo htmlspecialchars($row['username']); ?></h5>
                                <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Pic" class="content-img">
                                <div>
                                    <form method="POST">
                                        <input type="hidden" name="picture_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="like" class="like-dislike">👍 <span class="like-count"><?php echo $row['likes']; ?></span></button>
                                        <button type="submit" name="dislike" class="like-dislike">👎 <span class="like-count"><?php echo $row['dislikes']; ?></span></button>
                                    </form>
                                </div>
                                <form method="POST" class="mt-2">
                                    <input type="hidden" name="picture_id" value="<?php echo $row['id']; ?>">
                                    <input type="text" name="comment_text" placeholder="Add a comment..." required>
                                    <button type="submit" name="comment" class="btn btn-secondary">Comment</button>
                                </form>
                                <div class="comments mt-2">
                                    <h6>Comments:</h6>
                                    <?php
                                    $comments = $conn->query("SELECT comments.comment, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.picture_id = " . $row['id']);
                                    while ($comment_row = $comments->fetch_assoc()): ?>
                                        <p><strong><?php echo htmlspecialchars($comment_row['username']); ?>:</strong> <?php echo htmlspecialchars($comment_row['comment']); ?></p>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</main>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Picture</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="file" name="image" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel"><h4>Login</h4></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="login.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="loginUsername">Username</label>
                        <input type="text" class="form-control" id="loginUsername" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <input type="password" class="form-control" id="loginPassword" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>