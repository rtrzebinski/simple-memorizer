<ol class="breadcrumb">
    <li><a href="/">Home</a></li>
    <li class="active">Courses</li>
</ol>

<script type="text/javascript">
    $(document).ready(function() {
        $('#CourseTableContainer').jtable({
            title: 'Table of courses',
            paging: true, //Enable paging
            pageSize: 10, //Set page size (default: 10)
            sorting: true, //Enable sorting
            defaultSorting: 'courseId ASC', //Set default sorting
            actions: {
                listAction: '/jtable/listAction/courses',
                createAction: '/jtable/createAction/course',
                updateAction: '/jtable/updateAction/course',
                deleteAction: '/jtable/deleteAction/course'
            },
            fields: {
                courseId: {
                    key: true,
                    list: false
                },
                courseName: {
                    title: 'Course Name',
                    width: '20%',
                    create: true,
                    edit: true
                },
                manageCourse: {
                    title: 'Manage course',
                    width: '20%',
                    create: false,
                    edit: false,
                    display: function(data) {
                        return '<a href="<?= base_url() ?>course/manage/' + data.record.courseId + '">Manage</a>';
                    }
                },
                runCourse: {
                    title: 'Run course',
                    width: '20%',
                    create: false,
                    edit: false,
                    display: function(data) {
                        return '<a href="<?= base_url() ?>course/run/' + data.record.courseId + '">Run</a>';
                    }
                },
                courseQuestionsAmount: {
                    title: 'Questions',
                    width: '20%',
                    create: false,
                    edit: false
                },
                courseAveragePoints: {
                    title: 'Knowledge',
                    width: '20%',
                    create: false,
                    edit: false
                }
            }
        });

        $('#CourseTableContainer').jtable('load');
    });
</script>

<div id="CourseTableContainer"></div>