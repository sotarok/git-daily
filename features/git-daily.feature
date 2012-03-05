Feature: git-daily
  Git daily.

  Scenario: Version
    Given I am in a git repository "tmp/local"
    #When I run the command "git daily version"
     When I run "git daily version"
     Then It should contains:
       """
       git-daily: version
       """

  #Scenario: Initialization
  #  Given I am in a git repository "tmp/local"
  #    And I am on the git branch "master"
  #   When I run "git daily init" with:
  #     """



  #     """
  #   Then It should contains:
  #     """
  #     git-daily completed to initialize.
  #     """

  #Scenario: Already initialized
  #  Given I am in a git repository "tmp/local"
  #    And I am on the git branch "master"
  #   When I run "git daily init" with:
  #     """



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
