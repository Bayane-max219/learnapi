Feature: Course management API
  In order to manage educational content
  As an instructor
  I need to be able to create, read, update and delete courses

  Background:
    Given I am authenticated as an instructor

  Scenario: List all published courses
    Given there are 3 published courses
    When I send a GET request to "/api/courses"
    Then the response status code should be 200
    And the response should contain a JSON collection

  Scenario: Create a new course
    When I send a POST request to "/api/courses" with body:
      """
      {
        "title": "FastAPI pour les débutants",
        "description": "Apprenez FastAPI en pratique avec des exemples réels",
        "level": "beginner",
        "category": "python",
        "price": "0.00"
      }
      """
    Then the response status code should be 201
    And the response should contain "FastAPI pour les débutants"

  Scenario: Get a single course by ID
    Given a course with title "Django REST Framework" exists with id 1
    When I send a GET request to "/api/courses/1"
    Then the response status code should be 200
    And the response should contain "Django REST Framework"

  Scenario: Cannot create course with empty title
    When I send a POST request to "/api/courses" with body:
      """
      {
        "title": "",
        "description": "Some description",
        "level": "beginner",
        "category": "python",
        "price": "0.00"
      }
      """
    Then the response status code should be 422

  Scenario: Cannot create course with invalid level
    When I send a POST request to "/api/courses" with body:
      """
      {
        "title": "Valid Title Here",
        "description": "Some description here",
        "level": "expert",
        "category": "java",
        "price": "0.00"
      }
      """
    Then the response status code should be 422

  Scenario: Update an existing course
    Given a course with title "Old Title" exists with id 1
    When I send a PUT request to "/api/courses/1" with body:
      """
      {
        "title": "Updated Title",
        "description": "Updated description here",
        "level": "advanced",
        "category": "python",
        "price": "49.99"
      }
      """
    Then the response status code should be 200
    And the response should contain "Updated Title"

  Scenario: Delete a course
    Given a course with title "Course to Delete" exists with id 1
    When I send a DELETE request to "/api/courses/1"
    Then the response status code should be 204
