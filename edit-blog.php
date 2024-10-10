<?php
include 'layouts/session.php';
include 'layouts/main.php';
include 'layouts/config.php';
include 'layouts/functions.php';

if (!hasPermission('manage_blog') || !hasPermission('edit_blog')) {
    header('location: index.php');
    exit();
}

try {
    // Check if ID is provided
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $postID = $_GET['id'];

        // Prepare and execute SQL statement using a parameterized query
        $sql = "SELECT title, content, author, short_desc, front_image, inner_image_1, inner_image_2, created_by, created_at FROM blog_posts WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $postID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $title, $content, $author, $shortDesc, $frontImage, $firstInnerImage, $secondInnerImage, $createdBy, $createdAt);

        if (mysqli_stmt_fetch($stmt)) {
            $postData = array(
                'id' => $postID,
                'title' => $title,
                'content' => $content,
                'author' => $author,
                'short_desc' => $shortDesc,
                'front_image' => $frontImage,
                'inner_image_1' => $firstInnerImage,
                'inner_image_2' => $secondInnerImage,
                'created_by' => $createdBy,
                'created_at' => $createdAt
            );
        } else {
            throw new Exception("blog not found.");
        }
    } else {
        throw new Exception("blog ID not provided.");
    }
} catch (Exception $e) {
    $_SESSION['message'][] = array("type" => "error", "content" => "Error: " . $e->getMessage());
    header("Location: all-blogs.php");
    exit;
} finally {
    // Close the prepared statement and database connection
    if (isset($stmt)) mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Edit Blog Post | Mohsin Shaheen Construction Company</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
    <style>
        .ck-editor__editable[role="textbox"] {
            min-height: 200px;
        }
    </style>
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">
        <?php include 'layouts/menu.php'; ?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">mscc.pk</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Blogs</a></li>
                                        <li class="breadcrumb-item active">Edit Blog Post</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Edit Blog Post</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <form id="blogForm" method="post" action="all-blogs.php" enctype="multipart/form-data">
                                    <input type="hidden" name="postId" value="<?php echo $postID; ?>">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <div class="mb-2">
                                                <h4 class="header-title mt-2">Blog Editor</h4>
                                                <!-- Title field -->
                                                <div class="mb-3">
                                                    <label for="blogTitle" class="form-label">Title</label>
                                                    <input type="text" class="form-control" id="blogTitle" name="blogTitle" value="<?php echo htmlspecialchars($postData['title']); ?>" placeholder="Enter title">
                                                </div>
                                                <!-- Author field -->
                                                <div class="mb-3">
                                                    <label for="blogAuthor" class="form-label">Author</label>
                                                    <input type="text" class="form-control" id="blogAuthor" name="blogAuthor" value="<?php echo htmlspecialchars($postData['author']); ?>" placeholder="Enter author name">
                                                </div>
                                                <!-- Content field -->
                                                <div class="mb-3">
                                                    <label for="blogContent" class="form-label">Blog Content</label>
                                                    <textarea class="form-control" name="blogContent" id="blogContent" rows="5" placeholder="Enter blog content"><?php echo htmlspecialchars($postData['content']); ?></textarea>
                                                </div>
                                                <!-- Short Description field -->
                                                <div class="mb-3">
                                                    <label for="shortDesc" class="form-label">Short Description</label>
                                                    <input type="text" class="form-control" id="shortDesc" name="shortDesc" value="<?php echo htmlspecialchars($postData['short_desc']); ?>" placeholder="Enter short descritpion of blog.">
                                                </div>
                                                <!-- Front Image field -->
                                                <div class="mb-3">
                                                    <label for="frontImage" class="form-label">Front Image</label>
                                                    <input type="file" class="form-control" id="frontImage" name="frontImage" accept="image/*">
                                                    <img src="<?php echo htmlspecialchars($postData['front_image']); ?>" style="height: 100px;" alt="Front Image" class="img-fluid mt-2">
                                                </div>
                                                <!-- Main Image field -->
                                                <div class="mb-3">
                                                    <label for="inner_image_1" class="form-label">First Inner Image</label>
                                                    <input type="file" class="form-control" id="inner_image_1" name="inner_image_1" accept="image/*">
                                                    <img src="<?php echo htmlspecialchars($postData['inner_image_1']); ?>" style="height: 100px;" alt="Main Image" class="img-fluid mt-2">
                                                </div>
                                                <!-- Main Image field -->
                                                <div class="mb-3">
                                                    <label for="inner_image_2" class="form-label">Second Inner Image</label>
                                                    <input type="file" class="form-control" id="inner_image_2" name="inner_image_2" accept="image/*">
                                                    <img src="<?php echo htmlspecialchars($postData['inner_image_2']); ?>" style="height: 100px;" alt="Main Image" class="img-fluid mt-2">
                                                </div>
                                                <!-- Save button -->
                                                <button type="submit" name="btnUpdateBlog" class="btn btn-primary mt-3">Update</button>
                                            </div>
                                        </li>
                                    </ul>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'layouts/footer.php'; ?>
        </div>
    </div>
    <!-- END wrapper -->
    <?php include 'layouts/right-sidebar.php'; ?>
    <?php include 'layouts/footer-scripts.php'; ?>
    <!-- App js -->
    <script src="assets/js/app.min.js"></script>
    <!-- CKEditor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/ckeditor.js"></script>
    <script>
        CKEDITOR.ClassicEditor.create(document.getElementById("blogContent"), {
            toolbar: {
                items: [
                    'exportPDF', 'exportWord', '|',
                    'findAndReplace', 'selectAll', '|',
                    'heading', '|',
                    'bold', 'italic', 'strikethrough', 'underline', 'code', 'subscript', 'superscript', 'removeFormat', '|',
                    'bulletedList', 'numberedList', 'todoList', '|',
                    'outdent', 'indent', '|',
                    'undo', 'redo',
                    '-',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
                    'alignment', '|',
                    'link', 'uploadImage', 'blockQuote', 'insertTable', 'mediaEmbed', 'codeBlock', 'htmlEmbed', '|',
                    'specialCharacters', 'horizontalLine', 'pageBreak', '|',
                    'textPartLanguage', '|',
                    'sourceEditing'
                ],
                shouldNotGroupWhenFull: true
            },
            heading: {
                options: [{
                        model: 'paragraph',
                        title: 'Paragraph',
                        class: 'ck-heading_paragraph'
                    },
                    {
                        model: 'heading1',
                        view: 'h1',
                        title: 'Heading 1',
                        class: 'ck-heading_heading1'
                    },
                    {
                        model: 'heading2',
                        view: 'h2',
                        title: 'Heading 2',
                        class: 'ck-heading_heading2'
                    },
                    {
                        model: 'heading3',
                        view: 'h3',
                        title: 'Heading 3',
                        class: 'ck-heading_heading3'
                    },
                    {
                        model: 'heading4',
                        view: 'h4',
                        title: 'Heading 4',
                        class: 'ck-heading_heading4'
                    },
                    {
                        model: 'heading5',
                        view: 'h5',
                        title: 'Heading 5',
                        class: 'ck-heading_heading5'
                    },
                    {
                        model: 'heading6',
                        view: 'h6',
                        title: 'Heading 6',
                        class: 'ck-heading_heading6'
                    }
                ]
            },
            placeholder: 'Write the content of the blog here.',
            fontFamily: {
                options: [
                    'default',
                    'Arial, Helvetica, sans-serif',
                    'Courier New, Courier, monospace',
                    'Georgia, serif',
                    'Lucida Sans Unicode, Lucida Grande, sans-serif',
                    'Tahoma, Geneva, sans-serif',
                    'Times New Roman, Times, serif',
                    'Trebuchet MS, Helvetica, sans-serif',
                    'Verdana, Geneva, sans-serif'
                ],
                supportAllValues: true
            },
            fontSize: {
                options: [10, 12, 14, 'default', 18, 20, 22],
                supportAllValues: true
            },
            htmlSupport: {
                allow: [{
                    name: /.*/,
                    attributes: true,
                    classes: true,
                    styles: true
                }]
            },
            htmlEmbed: {
                showPreviews: true
            },
            link: {
                decorators: {
                    addTargetToExternalLinks: true,
                    defaultProtocol: 'https://',
                    toggleDownloadable: {
                        mode: 'manual',
                        label: 'Downloadable',
                        attributes: {
                            download: 'file'
                        }
                    }
                }
            },
            mention: {
                feeds: [{
                    marker: '@',
                    feed: [
                        '@apple', '@bears', '@brownie', '@cake', '@cake', '@candy', '@canes', '@chocolate', '@cookie', '@cotton', '@cream',
                        '@cupcake', '@danish', '@donut', '@dragée', '@fruitcake', '@gingerbread', '@gummi', '@ice', '@jelly-o',
                        '@liquorice', '@macaroon', '@marzipan', '@oat', '@pie', '@plum', '@pudding', '@sesame', '@snaps', '@soufflé',
                        '@sugar', '@sweet', '@topping', '@wafer'
                    ],
                    minimumCharacters: 1
                }]
            },
            removePlugins: [
                'AIAssistant', 'CKBox', 'CKFinder', 'EasyImage', 'Base64UploadAdapter', 'MultiLevelList', 'RealTimeCollaborativeComments',
                'RealTimeCollaborativeTrackChanges', 'RealTimeCollaborativeRevisionHistory', 'PresenceList', 'Comments', 'TrackChanges',
                'TrackChangesData', 'RevisionHistory', 'Pagination', 'WProofreader', 'MathType', 'SlashCommand', 'Template', 'DocumentOutline',
                'FormatPainter', 'TableOfContents', 'PasteFromOfficeEnhanced', 'CaseChange'
            ]
        });
    </script>

    <!-- Toastr Initialization -->
    <script>
        <?php
        if (isset($_SESSION['message'])) {
            foreach ($_SESSION['message'] as $message) {
                echo "toastr." . $message['type'] . "('" . $message['content'] . "');";
            }
            unset($_SESSION['message']); // Clear messages after displaying
        }
        ?>
    </script>

</body>

</html>