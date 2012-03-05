Feature: git-daily
  Git daily.

  Scenario: Version
    Given I am in a git repository "tmp/local"
     When I run "git daily version"
     Then It should contains:
       """
       git-daily: version
       """

  Scenario: No subcommand specified
    Given I am in a git repository "tmp/local"
      And I am on the git branch "master"
     When I run "git daily"
     Then It should contains:
       """
       Fatal: No subcommand specified
       """

  Scenario: No such command
    Given I am in a git repository "tmp/local"
      And I am on the git branch "master"
     When I run "git daily aoi"
     Then It should contains:
       """
       No such subcommand: aoi
       """

  Scenario: Initialization
    Given I am in a git repository "tmp/local"
      And I am on the git branch "master"
     When I run "git daily init --master master --develop develop"
     Then It should contains:
       """
       completed to initialize
       """

  Scenario: Already initialized
    Given I am in a git repository "tmp/local"
      And I am on the git branch "master"
     When I run "git daily init"
     Then It should contains:
       """
       Fatal: git-daily already initialized
       """

  #     """
  #   Then It should fails and contains:
  #     """
  #     git-daily already initialized.
  #     """

  #Scenario: Git Daily
  #  Given I am in a git repository "tmp/local"
  #   When I run "git daily"
  #   Then It should fail
  #   And  It should contains:
  #   """
  #   no subcommand specified.
  #   """
