@community
Feature: Edit answer modal

    Scenario: Wrong user group
        Given the user who clicked "Add answer" is not a "Seller" or a "Buyer" or a "Manufacturer" or a "Shipper"
        When the popup opens
        Then the content of the popup is "Error: You do not have permission to perform this action."

    Scenario: Wrong user group
        Given the user who clicked "Add answer" is a "Seller" or a "Buyer" or a "Manufacturer" or a "Shipper"
        But in another tab he logged out and logged as a usergroup of none of the above
        When he returns to the add answer popup
        And clicks "Send"
        Then the message is "Error: You do not have permission to perform this action."

    Background:
        Given the user who clicked "Add answer" is a "Seller" or a "Buyer" or a "Manufacturer" or a "Shipper"

    Scenario: No answer on open modal
        Given the answer does not exist (maybe it was deleted before the user opened the popup)
        When the user opens the popup
        Then the message in modal shows: "Error: The answer does not exist."

    Scenario: Answer moderated
        Given the answer has been moderated by the admin
        When the user opens the popup
        Then the message in modal shows: "Error: The answer has been moderated and can not be edited."

    Scenario: No answer info
        Given the user used the inspect element to delete the hidden input with the answer id
        When the user clicks "Send"
        Then the message is: "The \"Answer info\" is required."

    Scenario: No title
        Given the user did not complete the message and used the inspect element to delete the class of the title input
        When the user clicks "Send"
        Then the message is: "The \"Title\" is required."

    Scenario: No text
        Given the user did not complete the Answer body input and used the inspect element to delete the class of the textarea
        When the user clicks "Send"
        Then the message is: "The \"Text\" is required."

    Scenario: No answer on Send
        Given the answer does not exist (maybe it was deleted after the user opened the popup)
        When the user clicks "Send"
        Then the message is: "Error: The answer does not exist."

    Scenario: Answer moderated on Send
        Given the answer has been moderated by the admin
        When the user clicks "Send"
        Then the message is: "Error: The answer has been moderated and can not be edited."

    Scenario: Text too long
        Given the user completed all the fields
        When the user uses inspect element to delete the class of the answer textarea
        And then user deletes all textcounter event listeners using inspect element for the answer textarea
        And then user writes the text longer than 5000 characters
        And then clicks "Send"
        Then the message appears: "Field \"Text\" cannot contain more than 5000 characters."

    Scenario: Success
        Given the user has completed the form correctly
        When the user clicks "Send"
        Then the message is: "Your answer has been successfully updated"
        But if there is a error then the message is: "Error: The answer has not been updated. Please try again later."

    Scenario: Close
        When the user clicks the close button when the form is partially or fully completed
        Then the info confirm modal appears: "Are you sure you want to close this window?"
