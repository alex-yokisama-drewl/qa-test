<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        <meta charset="utf-8">
        <title>QA Test</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
        <style media="screen">
            .list-group-item:hover {
                background-color: rgba(13, 110, 253, 0.1);
            }
        </style>
    </head>
    <body>
        <main>
            <div class="container">
                <div class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">
                    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
                        <span class="fs-4">Polls</span>
                    </a>

                    <ul class="nav nav-pills">
                        <li class="nav-item"><a href="#" data-sort-by="date" class="nav-sort nav-link active">Latest</a></li>
                        <li class="nav-item"><a href="#" data-sort-by="is_hot" class="nav-sort nav-link">Hottest</a></li>
                    </ul>
                </div>
                <div id="poll-container">
                </div>
            </div>
        </main>
        <script>
            let polls = [];

            const renderPolls = () => {
                let container = document.getElementById('poll-container');

                if (!container) {
                    console.log('No container');
                    return;
                }

                html = '';

                polls.forEach((poll) => {console.log(poll);
                    let total = 0;
                    if (poll.choices) {
                        total = poll.choices.reduce((sum, choice) => {
                            return sum + Number.parseInt(choice.count);
                        }, 0);
                    }


                    html += `
                    <div class="my-4 mx-auto card w-50" >
                        <div class="card-body bg-primary bg-opacity-10">
                            <h5 class="card-title">${poll.question}</h5>
                            <ul class="list-group">
                    `;

                    if (poll.choices) {
                        poll.choices.forEach((choice) => {
                            html += `
                            <li class="list-group-item text-nowrap ${poll.voted ? '' : 'poll-choice'} ${((poll.voted == choice.id) ? 'bg-primary bg-opacity-10': '')}" style="cursor: pointer" data-poll-id="${poll.id}" data-choice-id="${choice.id}">
                                <div class="my-2 d-flex justify-content-between align-items-start">
                                    <div class="">
                                        ${choice.text}
                                    </div>
                            `;
                            if (poll.voted) {console.log(choice.count / total);
                                let percentage = Math.round(choice.count / total * 100) / 100;
                                let width = choice.count / total * 100;
                                html += `
                                        <span class="badge bg-primary rounded-pill">${choice.count}</span>
                                    </div>
                                    <div class="progress my-2">
                                        <div class="progress-bar" style="width: ${width}%;">${percentage}%</div>
                                    </div>
                                `;
                            }

                            html += `</li>`;
                        });
                    }

                    html += `
                            </ul>
                        </div>
                    </div>
                    `;
                });

                container.innerHTML = html;
                attachEventListeners();
            }

            const attachEventListeners = () => {
                document.querySelectorAll('.poll-choice').forEach((el) => {
                    el.addEventListener('click', () => {
                        let poll_id = el.dataset.pollId;
                        let choice_id = el.dataset.choiceId;

                        fetch(`/wp-json/api/poll?id=${poll_id}&choice_id=${choice_id}`).then(() => {
                            polls.forEach((poll) => {
                                if (poll.id == poll_id) {
                                    poll.voted = choice_id;
                                    return false;
                                }
                            });

                            renderPolls();
                        });
                    });
                });
            }

            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.nav-sort').forEach((el) => {
                    el.addEventListener('click', (e) => {
                        e.preventDefault();

                        document.querySelectorAll('.nav-sort').forEach((el) => {
                            el.classList.remove('active');
                        });

                        el.classList.add('active');
                    });
                });

                fetch('/wp-json/api/polls').then((response) => {
                    return response.json();
                }).then((data) => {
                    polls = data;
                    renderPolls();
                });
            });
        </script>
    </body>
</html>
