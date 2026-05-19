Feature: Course enrollment
  In order to access course content
  As a student
  I need to enroll in courses

  Background:
    Given I am authenticated as a student

  Scenario: Student enrolls in a course
    Given a published course with id 1 exists
    When I send a POST request to "/api/enrollments" with body:
      """
      {
        "course": "/api/courses/1"
      }
      """
    Then the response status code should be 201
    And the response should contain "enrolledAt"

  Scenario: Student cannot enroll twice in the same course
    Given I am already enrolled in course with id 1
    When I send a POST request to "/api/enrollments" with body:
      """
      {
        "course": "/api/courses/1"
      }
      """
    Then the response status code should be 422

  Scenario: Student can view own enrollments
    Given I have 2 active enrollments
    When I send a GET request to "/api/enrollments"
    Then the response status code should be 200
    And the response should contain a JSON collection
