<ol class="breadcrumb">
    <li><a href="/">Home</a></li>
    <li class="active"><?= $strCourseName ?></li>
</ol>

<label for="actions">Actions</label>
<div id="actions">
    <a class="btn btn-default btn-lg" href="/course/run/<?= $intCourseId ?>" role="button">Run course</a>
</div></br>

<script type="text/javascript">
    function htmlEntities(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    $(document).ready(function() {
        $('#QuestionTableContainer').jtable({
            title: '<?= $strCourseName ?>',
            paging: true, //Enable paging
            pageSize: 10, //Set page size (default: 10)
            sorting: true, //Enable sorting
            defaultSorting: 'questionId ASC', //Set default sorting
            actions: {
                listAction: '/jtable/listAction/questions/?courseId=<?= $intCourseId ?>',
                createAction: '/jtable/createAction/question/?courseId=<?= $intCourseId ?>',
                updateAction: '/jtable/updateAction/question',
                deleteAction: '/jtable/deleteAction/question'
            },
            fields: {
                questionId: {
                    key: true,
                    list: false
                },
                questionKey: {
                    title: 'Key',
                    width: '40%',
                    create: true,
                    edit: true,
                    type: 'textarea'
                },
                questionValue: {
                    title: 'Value',
                    width: '40%',
                    create: true,
                    edit: true,
                    type: 'textarea',
                    display: function(data) {
                        return htmlEntities(data.record.questionValue);
                    }
                },
                questionPoints: {
                    title: 'Knowledge',
                    width: '10%',
                    create: false,
                    edit: false
                }
            }
        });

        $('#QuestionTableContainer').jtable('load');
    });
</script>

<div id="QuestionTableContainer"></div></br>

<form method="POST">
    Merge current course with: <select name="courseId">
        <option value="">---</option>
        <?php foreach ($oRemainingCourses as $oCourse): ?>
        <option value="<?= $oCourse->getId() ?>"><?= $oCourse->getName() ?></option>
        <?php endforeach; ?>
    </select>
    <input class="btn btn-xs btn-default" type="submit" value="Merge" name="merge" />
</form>