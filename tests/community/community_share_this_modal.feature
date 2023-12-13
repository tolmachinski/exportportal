@community
Feature: Share this question modal

    Scenario: Wrong user group
        Given the user who clicked "Share this" is not a "Seller" or a "Buyer" or a "Manufacturer"
        When the popup opens
        Then the content of the popup is "Error: You do not have permission to perform this action."

    Scenario: Wrong user group
        Given the user who clicked "Share this" is a "Seller" or a "Buyer" or a "Manufacturer"
        But in another tab he logged out and logged as a usergroup of none of the above
        When he returns to the share this popup
        And clicks "Submit"
        Then the message is "Error: You do not have permission to perform this action."

    Background:
        Given the user who clicked "Share this" is a "Seller" or a "Buyer" or a "Manufacturer"

    Scenario: No question on open modal
        Given the question does not exist (maybe it was deleted before the user opened the popup)
        When the user opens the popup
        Then the message in modal shows: "Error: The question does not exist."

    Scenario: No question info
        Given the user used the inspect element to delete the hidden input with the question id
        When the user clicks "Submit"
        Then the message is: "The \"Question info\" is required."

    Scenario: No message
        Given the user did not complete the message and used the inspect element to delete the class of the message textarea
        When the user clicks "Submit"
        Then the message is: "The \"Message\" is required."

    Scenario: No question on submit
        Given the question does not exist (maybe it was deleted after the user opened the popup)
        When the user clicks "Submit"
        Then the message in modal shows: "Error: The question does not exist."

    Scenario: No followers
        Given the user has no followers and completed the message textarea
        When the user clicks "Submit"
        Then the message in modal shows: "You have no followers. Your message has not been sent."

    Scenario: Success
        Given the user has followers and completed the message textarea
        When the user clicks "Submit"
        Then the message in modal shows: "Your email was successfully sent."

    Scenario: Close
        When the user clicks the close button when the form is partially or fully completed
        Then the info confirm modal appears: "Are you sure you want to close this window?"
