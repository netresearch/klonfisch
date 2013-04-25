**********************************************
Klonfisch, the FishEye simulator for Gitorious
**********************************************

Implements the Atlassian FishEye__ API for Gitorious__, so that
you can view Gitorious Commits in JIRA__.

__ http://atlassian.com/software/fisheye/
__ https://gitorious.org/gitorious
__ http://atlassian.com/software/jira/

.. contents::

========
Features
========

- Simulates a part of the FishEye API
- Works with the JIRA FishEye plugin 5.0.7 and 5.0.10
- Shows commits on issues (Tab "Source")
- Shows commits for projects (Tab "Source")

Missing features
================
- Showing files that got changed in a commit. Waiting for
  `issue #128`__
- Project statistics
- Authentication - everyone can see all commits
- Activity streams

__ https://issues.gitorious.org/issues/128


===========
Screenshots
===========
.. figure:: doc/issue-source.png
   :height: 200px
   :target: doc/issue-source.png

   JIRA's issue source tab

.. figure:: doc/project-source.png
   :height: 200px
   :target: doc/project-source.png

   JIRA's project source tab


============
How it works
============
Klonfisch is a PHP application that sits between JIRA and Gitorious:

Gitorious sends commit information to Klonfisch which stores them
in a MySQL database.
This is done via "web hooks".

JIRA on the other hand talks to Klonfisch, asking for commit information
for projects or single issues.
Klonfisch searches in the commit database and returns them to JIRA.
JIRA then displays the commits in the "Source" tab of issues and projects.

You just have to install the FishEye plugin for JIRA, which takes care of
talking to Klonfisch and the commit visualization.


============
Dependencies
============

- PHP 5.3+ with modules pdo_mysql, pdo and xml (XMLWriter)
- Apache with ``mod_rewrite`` enabled
- MySQL (or any other database supported by PDO)


=====
Setup
=====

Klonfisch setup
===============
1. ``git clone`` the klonfisch git repository
2. Create a (MySQL) database to store the commits in
3. Import ``data/database.sql`` into that database
4. Copy ``data/klonfisch.config.php.dist`` to
   ``data/klonfisch.config.php`` and adjust it to your environment.
5. Setup your (apache) webserver by adding a virtual host and pointing its
   document root to ``$klonfisch/www/``
6. Change the owner of ``$klonfisch/www`` to your Apache user

Gitorious setup
===============
Klonfisch keeps record of commits to your Gitorious instance via web hooks.
You can setup them manually in the database, or let Klonfisch create the
hooks automatically.

Run ``scripts/update-gitorious-hooks.php`` ever hour to automatically
register the hook for newly created repositories.

Gitorious will then call ``/webhook-call.php`` for each single commit
to a repository.


JIRA setup
==========
1. Install the FishEye plugin. Just installation, no configuration
2. Go to Administration / Plugins / Application Links
3. Click "Add Application Link"
4. Set the Server URL, e.g. ``http://klonfisch.gitorious.company.com/``
5. Disable ``Also create a link from "klonfisch" back to to this server``
6. Finish the application link setup

That's it. You do not need to setup any authentication.
You do not need to setup any project connections.

Now do a commit, mentioning the issue number (e.g. "JGA-11") in the commit
message.
You will see the commit in JIRA's "Source" tab.


============
Known issues
============

Also see `Missing features`_.

Commits on new branches are not shown
=====================================
When creating a new branch, committing on it and then pushing it to Gitorious,
Klonfisch does not show the commits.

Reason for this is `bug #166`__ in Gitorious; we do not get information
about that commits.

You can work around the bug by pushing the branch directly after creating it,
and committing to it afterwards.

__ https://issues.gitorious.org/issues/166


Clicking on repository links does not work
==========================================
Klonfisch simulates only one git repository, mainly to reduce the number
of requests from JIRA.
(helpful if you have 700+ repositories, and 200+ repositories for a single
JIRA project)
This leads to the issue that only the repository "test" is shown for
the commits, even though they are from a different repository.

Use the branch link instead (``master in $project/$repo``).


Removing application links
==========================

After removing an application link, you need to disable the
FishEye plugin and re-enable it again.

If you fail to do so, you will see errors like

 This list may be incomplete, as errors occurred whilst retrieving
 source from linked applications:

 Repository test on http://klonfisch.gitorious.nr/ failed:
 The application link with id '46bc9c7c-0bad-3503-9ddf-0123456789ab'
 was not found for instance 'FishEyeInstanceImpl...'


Crucible links + buttons in JIRA
================================
You will see "Create Crucible reviews" links in JIRA's issue tab.

I have no idea how to deactivate them.
If you know how, tell me.



===============
About Klonfisch
===============

License
=======
Klonfisch is licensed under the `AGPL v3`__ or later.

__ http://www.gnu.org/licenses/agpl


Author
======
Christian Weiske, `Netresearch GmbH & Co KG`__

__ http://www.netresearch.de/


Homepage
========
Klonfisch is available at https://github.com/netresearch/klonfisch
