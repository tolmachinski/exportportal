@community
Feature: On clicking the button "Ask a question" a modal opens up.

    Scenario: Wrong user group
        Given the user who clicked "Ask a question" is not a "Seller" or a "Buyer" or a "Manufacturer"
        When the popup opens
        Then the content of the popup is "Error: You do not have permission to perform this action."

    Background:
        Given the user who clicked "Ask a question" is a "Seller" or a "Buyer" or a "Manufacturer"

    Scenario: Form not completed
        When the user did not complete any field
        Then the message appears: "Some errors appeared during form completion. Please make sure all the required fields are listed."
        And the empty inputs get red border

    Scenario: No language
        Given the user didn't submit his form yet
        When the user uses inspect element to delete one of the Language select's option values
        And then he changes the language input to that option he edited
        Then the message appears: "Field \"Language\" is required."

    Scenario: No country
        Given the user completed all the fields
        When the user uses inspect element to delete the name and the class of the country select
        And then clicks "Submit"
        Then the message appears: "Field \"Country\" is required."

    Scenario: No category
        Given the user completed all the fields
        When the user uses inspect element to delete the name and the class of the category select
        And then clicks "Submit"
        Then the message appears: "Field \"Category\" is required."

    Scenario: No title
        Given the user completed all the fields
        When the user uses inspect element to delete the name and the class of the title input
        And then clicks "Submit"
        Then the message appears: "Field \"Title\" is required."

    Scenario: No text
        Given the user completed all the fields
        When the user uses inspect element to delete the name and the class of the text textarea
        And then clicks "Submit"
        Then the message appears: "Field \"Text\" is required."

    Scenario: Title too long
        Given the user completed all the fields
        When the user uses inspect element to delete the class of the title input
        And then user deletes all textcounter event listeners from inspect element for the title input
        And then user writes the title longer than 100 characters
        And then clicks "Submit"
        Then the message appears: "Field \"Title\" cannot contain more than 100 characters."

    Scenario: Text too long
        Given the user completed all the fields
        When the user uses inspect element to delete the class of the text textarea
        And then user deletes all textcounter event listeners from inspect element for the text textarea
        And then user writes the text longer than 1000 characters
        And then clicks "Submit"
        Then the message appears: "Field \"Text\" cannot contain more than 1000 characters."

    Scenario:
        Given the user didn't submit his form yet
        When the user uses inspect element to change one of the select's option values to an inexistent language
        And then he changes the language input to that option he edited
        Then the message appears: "Error: Language does not exist."

    Scenario: No such language
        Given the user completed all the fields
        When the user uses inspect element to change the selected language' option value to an inexistent language
        And he clicks "Submit"
        Then the message appears: "Error: The language does not exist"

    Scenario: No such category
        Given the user completed all the fields
        When the user uses inspect element to delete or to change the selected category' option value to an inexistent category id
        And he clicks "Submit"
        Then the message appears: "Error: The category does not exist or is not available for this language."

    Scenario: No such country
        Given the user completed all the fields
        When the user uses inspect element to delete or to change the selected country' option value to an inexistent country id
        And he clicks "Submit"
        Then the message appears: "Error: The country does not exist."

    Scenario: Success or Error
        Given the user completed all the fields with the right data
        When clicks "Submit"
        Then the message appears: "Your question has been successfully saved."
        But also this message may appear if something went wrong on saving: "Error: Your question has not been saved. Please try again later."

    Scenario: Close
        When the user clicks the close button when the form is partially or fully completed
        Then the info confirm modal appears: "Are you sure you want to close this window?"

