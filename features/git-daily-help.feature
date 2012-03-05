Feature: git-daily help
  Git daily help

  Scenario: Help
    Given I am in a git repository "tmp/local"
     When I run "git daily help"
     Then It should contains:
       """
       git-daily:

       Usage:
           version    Show git-daily version
       """

  Scenario: Help init
    Given I am in a git repository "tmp/local"
     When I run "git daily help init"
     Then It should contains:
       """
       Initialize git-daily repository. Set up git-daily branching model (like gitflow).
       If any options are not given, setup interactive.

       Usage:
           git daily init [--master <master_name>] [--develop <develp_name>] [--remote <remote_name>]

       Options:

           --master
               Branch name to use as master. Master branch is always use as a released branch.

           --develop
               Branch name to use as develop. Develop branch is use to development.

           --remote
               Collaborate your development with some remote repository, set your remote name.
       """

  Scenario: Help config
    Given I am in a git repository "tmp/local"
     When I run "git daily help config"
     Then It should contains:
       """
       Usage: git daily config <key> <value>

       Example:

           Remote name :
               git daily config remote origin

           Branch name of develop :
               git daily config develop develop

           Branch name of master :
               git daily config master master

           URL template for dump list (will dump commit hash instead of "%s") :
               GitWeb :  git daily config logurl "http://example.com/?p=repositories/example.git;a=commit;h=%s"
               GitHub :  git daily config logurl "https://github.com/sotarok/git-daily/commit/%s"
       """

  Scenario: Help push
    Given I am in a git repository "tmp/local"
     When I run "git daily help push"
     Then It should contains:
       """
       Usage:
           git daily push
       """

  Scenario: Help pull
    Given I am in a git repository "tmp/local"
     When I run "git daily help pull"
     Then It should contains:
       """
       Usage:
           git daily pull [--rebase]

       Options:

           --rebase
               Rebase remote branch instead of merge.
       """
