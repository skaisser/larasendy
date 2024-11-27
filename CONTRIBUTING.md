# Contributing to LaraSendy

First off, thank you for considering contributing to LaraSendy! It's people like you that make LaraSendy such a great tool.

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## Development Process

We use GitHub to host code, to track issues and feature requests, as well as accept pull requests.

1. Fork the repo and create your branch from `main`.
2. If you've added code that should be tested, add tests.
3. If you've changed APIs, update the documentation.
4. Ensure the test suite passes.
5. Make sure your code lints.
6. Issue that pull request!

## Pull Request Process

1. Update the README.md with details of changes to the interface, if applicable.
2. Update the CHANGELOG.md with a note describing your changes.
3. The PR will be merged once you have the sign-off of at least one other developer.

## Setting Up Development Environment

1. Clone your fork of the repository:
```bash
git clone https://github.com/YOUR-USERNAME/larasendy.git
```

2. Install dependencies:
```bash
composer install
```

3. Set up your test environment:
```bash
cp .env.example .env.testing
```

4. Run the tests:
```bash
composer test
```

## Testing

We use PHPUnit for testing. All tests are located in the `tests` directory. To run tests:

```bash
composer test
```

### Writing Tests

- Place tests in the appropriate directory under `tests/`
- Follow the naming convention: `*Test.php`
- Test both success and failure cases
- Mock external services (like Sendy API) using the provided test helpers

Example test structure:
```php
/** @test */
public function it_does_something_specific()
{
    // Arrange
    $data = ['key' => 'value'];

    // Act
    $result = $this->someMethod($data);

    // Assert
    $this->assertTrue($result);
}
```

## Coding Standards

We follow the PSR-12 coding standard and the PSR-4 autoloading standard.

- Use PHP CS Fixer to ensure code style compliance
- Keep functions small and focused
- Write descriptive variable and function names
- Add comments for complex logic
- Use type hints and return type declarations

## Documentation

- Keep the README.md up to date
- Document all public methods and classes
- Include examples in docblocks
- Update the changelog

## Reporting Bugs

Report bugs using GitHub's [issue tracker](https://github.com/skaisser/larasendy/issues)

**Great Bug Reports** tend to have:

- A quick summary and/or background
- Steps to reproduce
  - Be specific!
  - Give sample code if you can
- What you expected would happen
- What actually happens
- Notes (possibly including why you think this might be happening, or stuff you tried that didn't work)

## Feature Requests

We love feature requests! Here's the process:

1. Check if the feature has already been requested or implemented
2. Clearly describe the use case
3. Explain how your feature would work
4. Remember that this is an open source project, and that features take time and effort

## License

By contributing, you agree that your contributions will be licensed under its MIT License.

## Questions?

Don't hesitate to ask questions about contributing. You can:

- Open an issue
- Contact the maintainers
- Join our community discussions

Thank you for contributing to LaraSendy! 🚀
