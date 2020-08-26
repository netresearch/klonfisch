// ==UserScript==
// @name        Klonfisch commits in JIRA cloud
// @version     2
// @description Display commit history on the Jira ticket details page
// @include     /https:\/\/.*\.atlassian\.net\/browse\/.*/
// @icon        http://klonfisch/favicon.ico
// ==/UserScript==


window.addEventListener('load', function () {

    var ticket = window.location.href.split('/').slice(-1)[0];
    if (ticket.includes('?')) {
        ticket = ticket.split('?')[0];
    }

    var url = 'http://klonfisch/search.php?q=' + ticket;
    var script = document.createElement('script');
    addLabel(url);

}, false)


function addLabel(url) {

    if (document.getElementById('activitymodule') !== null) {
        var activitymodule = document.getElementById('activitymodule');
        var lastEl = document.getElementById('addcomment');
        var parent = activitymodule.parentElement;

        var newEl = createNewElement("div")
        newEl.id = "commit-history";
        newEl.classList.add("module", "toggle-wrap", "collapsed");

        var headline = createNewElement("h2", "Source");
        headline.id = "commit-headline";
        headline.classList.add("toggle-title");
        newEl.appendChild(headline);

        var content = createNewElement("div");
        content.id = "commit-content"
        content.classList.add("mod-content");
        newEl.appendChild(content);

        newEl.addEventListener('click', function () {
            getCommitHistory.bind({
                url: url,
            })();
        });

        parent.insertBefore(newEl, lastEl);
        dropDownStyle();
    }
}

function createNewElement(el, content = null) {

    var newEl = document.createElement(el);
    if (content !== null) {
        var newContent = document.createTextNode(content);
        newEl.appendChild(newContent);
    }
    return newEl
}


function getCommitHistory() {
    var toggle = document.getElementById("commit-history");

    if (toggle.classList.contains("collapsed")) {
        toggle.classList.remove("collapsed");
    } else {
        toggle.classList.add("collapsed");
    }

    var content = document.getElementById("commit-content");

    fetch(this.url)
        .then(response => {
            if (!response.ok) {
                throw new Error(response.statusText);
            }
            return response.text();
        }).then(function (html) {

            if (html != undefined) {
                const data = html.split('</form>').pop();
                content.innerHTML = data;
                historyStyle();
            } else {
                content = "<p>No related source found.</p>";
            }

        })
        .catch(e => {
            content.innerHTML = "Request failed: " + e;
            console.log("Request failed: ", e);
        })
}

function addGlobalStyle(css) {
    var head, style;
    head = document.getElementsByTagName('head')[0];
    if (!head) {
        return;
    }
    style = document.createElement('style');
    style.type = 'text/css';
    style.innerHTML = css;
    head.appendChild(style);
}

function dropDownStyle() {

    addGlobalStyle('#commit-headline {font-size: inherit;}');
}


function historyStyle() {

    addGlobalStyle('.aui-iconfont-branch::before{ content: "\\f127";}');

    addGlobalStyle('.nav a{ font-weight: bold;'
        + 'color: #172B4D;}');

    addGlobalStyle('.branch { color: #333 ;'
        + 'border-radius: 3px;}');

    addGlobalStyle('.repository { background: #f5f5f5;'
        + 'font-size: 13px; border: 1px solid #ccc;'
        + 'padding: 4px 4px 4px 22px;'
        + 'margin: 0 3px 3px;'
        + 'font-family: Courier,"Courier New",monospace;'
        + 'height: 16px; line-height: 16px;'
        + 'border-radius: 3px; vertical-align: middle;}');

    addGlobalStyle('.commit_files_container  { background-color: #fff;'
        + 'margin-top: 10px; padding: 10px;'
        + 'border: 1px solid #ccc;'
        + 'font-family: Courier,"Courier New",monospace;}');

    addGlobalStyle('.nav  {   margin-bottom: 10px;}');

    addGlobalStyle('.commit { border-radius: 3px;display: inline-block;'
        + 'overflow: hidden;text-overflow: ellipsis;'
        + 'white-space: nowrap;overflow: hidden;'
        + 'max-width: 74px;vertical-align: bottom;}');

    addGlobalStyle('.publish { color: #707070 !important; font-size: 12px !important;}');

    addGlobalStyle('.commit_details { background-color: #f5f5f5;'
        + 'padding: 20px; text-decoration: none;}');
}
