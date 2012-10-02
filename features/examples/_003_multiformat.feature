@example3 @multiformat
Feature: Testing Multi-format Example

  Scenario: Default format, when not specified
    When I request "/examples/_003_multiformat/bmi"
    Then the response status code should be 200
    And the response is JSON

  Scenario: JSON format by extension
    When I request "/examples/_003_multiformat/bmi.json"
    Then the response status code should be 200
    And the response is JSON

  Scenario: XML format by extension
    When I request "/examples/_003_multiformat/bmi.xml"
    Then the response status code should be 200
    And the response is XML
    And the type is "array"

  Scenario: XML format by Accept Header
    Given that "Accept" header is set to "application/xml"
    When I request "/examples/_003_multiformat/bmi"
    Then the response status code should be 200
    And the response is XML
    And the type is "array"

  Scenario: Content Negotiation
    Given that "Accept" header is set to "application/json;q=0.8,application/xml"
    When I request "/examples/_003_multiformat/bmi"
    Then the response status code should be 200
    And the response is XML
    And the type is "array"

  Scenario: Content Negotiation
    Given that "Accept" header is set to "application/json;q=0.8,application/xml;q=0.4"
    When I request "/examples/_003_multiformat/bmi"
    Then the response status code should be 200
    And the response is JSON

  Scenario: Choose first format when both are OK
    Given that "Accept" header is set to "application/xml,application/json"
    When I request "/examples/_003_multiformat/bmi"
    Then the response status code should be 200
    And the response is XML
    And the type is "array"
    And the response has a "bmi" property
    And the "message" property equals "Obesity"

  Scenario: Correct weight and height should yeild 'Normal weight' as result
    Given that "height" is set to 180
    And "weight" is set to 80
    When I request "/examples/_003_multiformat/bmi.xml{?height,weight}"
    Then the response status code should be 200
    And the "message" property equals "Normal weight"