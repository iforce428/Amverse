<?php
session_start(); // Start session
require_once('config.php');
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once('inc/header.php'); ?>

<style>
    #convo-box {
        height: 35em;
        display: flex;
        flex-direction: column-reverse;
    }

    #suggestion-list:not(:empty):before {
        content: 'Suggestions';
        width: 100%;
        display: block;
        color: #ababab;
        padding: 0.6em 1em;
    }

    .msg-field {
        min-width: 5em;
    }

    .msg-field.bot-msg {
        background: #f1f1f1 !important;
    }

    .rounded-pill {
        border-radius: 2rem !important;
    }

    #keyword,
    #submit {
        pointer-events: <?= isset($_SESSION['customer_id']) ? 'auto' : 'none' ?>;
        opacity: <?= isset($_SESSION['customer_id']) ? '1' : '0.5' ?>;
    }

    .chat-disabled-overlay {
        display: <?= isset($_SESSION['customer_id']) ? 'none' : 'flex' ?>;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        justify-content: center;
        align-items: center;
        font-size: 18px;
        font-weight: bold;
        color: #000;
        z-index: 2;
    }

    .chat-disabled-overlay a {
        color: #007bff;
        text-decoration: none;
        font-weight: bold;
    }

    .chat-disabled-overlay a:hover {
        text-decoration: underline;
    }
</style>

<body>
    <div class="container my-5">
        <div class="card card-outline-navy rounded-0">
            <div class="card-header">
                <div class="d-flex w-100 align-items-center">
                    <div class="col-auto">
                        <img src="<?= validate_image($_settings->info('logo')) ?>" class="img-fluid img-thumbnail rounded-circle" style="width:1.9em;height:1.9em;object-fit:cover;object-position:center center" alt="<?= validate_image($_settings->info('bot_name')) ?>">
                    </div>
                    <div class="col-auto">
                        <b><?= $_settings->info("bot_name") ?></b>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="overflow-auto" id="convo-box">
                    <?php if (!isset($_SESSION['customer_id'])): ?>
                        <div class="chat-disabled-overlay">
                            Please&nbsp;<a href="customer/customer_signin.php">Sign In</a>&nbsp;to use the chat.
                        </div>
                    <?php endif; ?>
                    <div id="suggestion-list" class="my-4 px-5">
                        <?php
                        $suggestions = $_settings->info('suggestion') != '' ? json_decode($_settings->info('suggestion')) : [];
                        foreach ($suggestions as $sg):
                            if (empty($sg))
                                continue;
                        ?>
                            <a href="javascript:void(0)" class="w-auto rounded-pill bg-transparent border px-3 py-2 text-decoration-none text-reset suggestion"><?= $sg ?></a>
                        <?php endforeach; ?>
                    </div>
                    <div class="d-flex w-100 align-items-center mt-4">
                        <div class="col-auto">
                            <img src="<?= validate_image($_settings->info('logo')) ?>" class="img-fluid img-thumbnail rounded-circle" style="width:2.5em;height:2.5em;object-fit:cover;object-position:center center" alt="<?= validate_image($_settings->info('bot_name')) ?>">
                        </div>
                        <div class="col-auto flex-shrink-1 flex-grow-1">
                            <div class="msg-field bot-msg w-auto d-inline-block bg-gradient-light border rounded-pill px-3 py-2"><?= $_settings->info('welcome_message') ?></div>
                        </div>
                    </div>
                </div>
                <div class="d-flex w-100 align-items-center">
                    <div class="col-auto flex-shrink-1 flex-grow-1">
                        <textarea name="keyword" id="keyword" cols="30" class="form-control form-control-sm rounded-0" placeholder="Write your query here" rows="2"></textarea>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-primary border-0 rounded-0" type="button" id="submit"><i class="fa fa-paper-plane"></i></button>
                    </div>
                </div>
                <div class="d-flex w-100 mt-3">
                    <button class="btn btn-success w-100" id="save-conversation-btn" <?= isset($_SESSION['customer_id']) ? '' : 'disabled' ?>>Save Conversation</button>
                </div>
            </div>
        </div>
    </div>
    <noscript id="resp-msg">
        <div class="d-flex w-100 align-items-center mt-4">
            <div class="col-auto">
                <img src="<?= validate_image($_settings->info('logo')) ?>" class="img-fluid img-thumbnail rounded-circle" style="width:2.5em;height:2.5em;object-fit:cover;object-position:center center" alt="<?= validate_image($_settings->info('bot_name')) ?>">
            </div>
            <div class="col-auto flex-shrink-1 flex-grow-1">
                <div class="msg-field bot-msg w-auto d-inline-block bg-gradient-light border rounded-pill px-3 py-2 response"></div>
            </div>
        </div>
    </noscript>
    <noscript id="user-msg">
        <div class="d-flex flex-row-reverse  w-100 align-items-center mt-4">
            <div class="col-auto text-center">
                <div class="img-fluid img-thumbnail rounded-circle" style="width:2.5em;height:2.5em">
                    <i class="fa fa-user text-muted bg-gradient-light" style="font-size:1em"></i>
                </div>
            </div>
            <div class="col-auto flex-shrink-1 flex-grow-1 text-right">
                <div class="msg-field w-auto d-inline-block bg-gradient-light border rounded-pill px-3 py-2 msg text-left"></div>
            </div>
        </div>
    </noscript>
    <noscript id="sg-item">
        <a href="javascript:void(0)" class="w-auto rounded-pill bg-transparent border px-3 py-2 text-decoration-none text-reset suggestion"></a>
    </noscript>
    <script>
        // Add user and bot messages to the conversation array
        let conversation = []; // Store the current conversation
        function add_msg($kw = "", type = "user") {
            let item;

            if (type === "user") {
                item = $($("noscript#user-msg").html()).clone();
                item.find(".msg-field").text($kw);

                // Add to conversation array
                conversation.push({
                    type: "user",
                    message: $kw,
                });
            } else {
                item = $($("noscript#resp-msg").html()).clone();
                item.find(".msg-field").text($kw);

                // Add to conversation array
                conversation.push({
                    type: "bot",
                    message: $kw,
                });
            }

            $("#convo-box").prepend(item);
        }

        //functionality for fetch_response
        // function fetch_response($kw = "") {
        //     const item = $($("noscript#resp-msg").html()).clone();

        //     $.ajax({
        //         url: _base_url_ + "classes/Master.php?f=fetch_response",
        //         method: "POST",
        //         data: {
        //             kw: $kw
        //         },
        //         dataType: "json",
        //         error: (err) => {
        //             console.error(err);
        //             alert("An error occurred while fetching a response.");
        //         },
        //         success: function(resp) {
        //             if (resp.status === "success") {
        //                 // Add bot response to UI
        //                 item.find(".msg-field").html(resp.response);
        //                 $("#convo-box").prepend(item);

        //                 // Add bot message to the conversation array
        //                 conversation.push({
        //                     type: "bot",
        //                     message: resp.response,
        //                 });

        //                 // Clear and populate suggestions
        //                 const suggestionList = $("#suggestion-list");
        //                 suggestionList.empty();

        //                 if (resp.suggestions && resp.suggestions.length > 0) {
        //                     resp.suggestions.forEach((suggestion) => {
        //                         if (suggestion.trim() !== "") {
        //                             const suggestionItem = $(
        //                                 $("noscript#sg-item").html()
        //                             ).clone();
        //                             suggestionItem.text(suggestion);
        //                             suggestionItem.click(function() {
        //                                 const kw = $(this).text();
        //                                 add_msg(kw); // Add user query
        //                                 fetch_response(kw); // Fetch bot response
        //                             });
        //                             suggestionList.append(suggestionItem); // Changed to `append` for proper order
        //                         }
        //                     });
        //                 }
        //             } else {
        //                 alert("Failed to fetch response.");
        //             }
        //         },
        //     });
        // }

        function fetch_response($kw = "") {
            const item = $($("noscript#resp-msg").html()).clone();

            $.ajax({
                url: 'http://localhost/amverse/openai.php', // URL to openai.php
                method: "POST",
                data: {
                    user_query: $kw // Send the user query to openai.php
                },
                dataType: "json", // Fixed to JSON (not "application/json")
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', jqXHR, textStatus, errorThrown); // Log full error info
                    alert("An error occurred while fetching a response. Please check the console for details.");
                },
                success: function(resp) {
                    console.log('Response:', resp); // Log the response to check what's returned

                    if (resp && resp.content) {
                        // Extract content from the response
                        let responseText = resp.content || "Sorry, I couldn't get a response.";

                        // Insert the response as plain text
                        item.find(".msg-field").text(responseText);

                        // Add bot response to UI
                        $("#convo-box").prepend(item);

                        // Add bot message to the conversation array
                        conversation.push({
                            type: "bot",
                            message: responseText,
                        });

                        // Clear suggestions (if any)
                        const suggestionList = $("#suggestion-list");
                        suggestionList.empty();
                    } else {
                        alert("Failed to fetch a valid response.");
                    }
                }
            });
        }


        // Save the conversation to the database
        function save_conversation() {
            if (conversation.length === 0) {
                alert("No conversation to save.");
                return;
            }

            $.ajax({
                url: "customer/store_chat.php", // Endpoint for saving chats
                method: "POST",
                data: {
                    conversation: JSON.stringify(conversation), // Send full conversation
                },
                dataType: "json",
                success: function(resp) {
                    if (resp.status === "success") {
                        alert("Conversation saved successfully!");
                        conversation = []; // Clear the array after saving
                    } else {
                        alert("Failed to save the conversation. Please try again.");
                    }
                },
                error: function(err) {
                    console.error(err);
                    alert("An error occurred while saving the conversation.");
                },
            });
        }

        $(function() {
            // Handle Enter key press
            $('#keyword').on('keydown', function(e) {
            // Check if Enter was pressed without Shift key
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault(); // Prevent default newline
                const kw = $(this).val().trim();
                if (kw) {
                    add_msg(kw);
                    fetch_response(kw);
                    $(this).val('').focus();
                }
            }
        });

            // Trigger save conversation on button click
            $('#save-conversation-btn').click(function() {
                save_conversation();
            });

            $('#submit').click(function() {
                const kw = $('#keyword').val();
                add_msg(kw); // Add user query
                fetch_response(kw); // Fetch bot response
                $('#keyword').val('').focus(); // Clear input
            });

            $('.suggestion').click(function() {
                const kw = $(this).text();
                add_msg(kw); // Add user query from suggestion
                fetch_response(kw); // Fetch bot response
            });
        });
    </script>
</body>

</html>