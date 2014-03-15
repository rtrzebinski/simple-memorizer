<script type="text/javascript" language="JavaScript">
    $(function() {
        $("#showAnswerButton").click(function(event) {
            event.preventDefault();
            $("#questionValueDiv").removeClass("hidden");
            $("#showAnswerButton").hide();
        });
    });
</script>

<ol class="breadcrumb">
    <li><a href="/">Home</a></li>
    <li class="active"><?= $courseName ?></li>
</ol>

<form method="POST" class="form" role="form">
    <label for="questionActions">Actions</label>
    <div id="questionActions">
        <a class="btn btn-default btn-lg" href="/course/manage/<?= $courseId ?>" role="button">Manage course</a>&nbsp;&nbsp;&nbsp;
        <input class="btn btn-default btn-lg" type="submit" value="Update" name="update" />&nbsp;&nbsp;&nbsp;
        <input class="btn btn-default btn-success btn-lg" type="submit" value="Good" name="<?= ANSWER_GOOD ?>" />&nbsp;&nbsp;&nbsp;
        <input class="btn btn-default btn-danger btn-lg" type="submit" value="Bad" name="<?= ANSWER_BAD ?>" />&nbsp;&nbsp;&nbsp;
        <button id="showAnswerButton" class="btn btn-default btn-info btn-lg"/>Show answer</button>&nbsp;&nbsp;&nbsp;
        <input type="hidden" name="questionId" value="<?= $questionId ?>" />
    </div>
    <div>
        <br/>
        <label for="questionKeyTextarea">Question</label>
        <textarea id="questionKeyTextarea" class="form-control" name="questionKey" rows="3"><?= $questionKey ?></textarea><br/>
    </div>
    <div id="questionValueDiv" class="<?php echo $hideQuestionValue ? 'hidden' : '' ?>">
        <label for="questionValueTextarea">Answer</label>
        <textarea id="questionValueTextarea" class="form-control" name="questionValue" rows="5"><?= $questionValue ?></textarea><br/>
    </div>
</form>