@community
Feature: Search community using "Search" box from the sidebar

    Scenario: No search
        When user clicks "Search" button without completing the "Keywords" input
        Then Nothing happens and the keywords input box gets required validation outline

    Scenario: Minimum 3
        Given the user completes the "Keywords" input with a text shorter than 3 characters
        When the user uses inspect element to delete "keywords" input class attribute
        And the user clicks "Search"
        Then The page reloads, and a system message appears: "Error: The search keywords must have at least 3 characters."

    Scenario: Result
        Given the user completes the "Keywords" input with a text longer than 3 characters and shorter than 50 characters
        When the user clicks "Search" button
        Then The page reloads with the content matching the Search
        But If there are no results, then "No results" page will show
