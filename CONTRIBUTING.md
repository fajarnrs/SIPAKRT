# Contributing to Data Warga

First off, thank you for considering contributing to Data Warga! 🎉

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Pull Request Process](#pull-request-process)
- [Coding Standards](#coding-standards)
- [Testing](#testing)

## 📜 Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## 🤝 How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When you create a bug report, include as many details as possible:

- **Use a clear and descriptive title**
- **Describe the exact steps to reproduce the problem**
- **Provide specific examples**
- **Describe the behavior you observed and what you expected**
- **Include screenshots if relevant**
- **Include your environment details** (OS, PHP version, etc.)

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion:

- **Use a clear and descriptive title**
- **Provide a detailed description of the suggested enhancement**
- **Explain why this enhancement would be useful**
- **List some examples of how it would be used**

### Pull Requests

- Fill in the required template
- Follow the [coding standards](#coding-standards)
- Include appropriate test coverage
- Update documentation as needed
- End all files with a newline

## 💻 Development Setup

### Prerequisites

- PHP 8.1+
- Composer
- MySQL 8.0+
- Node.js 16+ (for frontend assets)
- Git

### Setup Steps

1. **Fork the repository**

2. **Clone your fork**
   ```bash
   git clone https://github.com/YOUR-USERNAME/data-warga.git
   cd data-warga
   ```

3. **Add upstream remote**
   ```bash
   git remote add upstream https://github.com/ORIGINAL-OWNER/data-warga.git
   ```

4. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

5. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

6. **Configure database**
   ```
   DB_DATABASE=pendataan_warga_dev
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

7. **Run migrations**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

8. **Build assets**
   ```bash
   npm run dev
   ```

9. **Start development server**
   ```bash
   php artisan serve
   ```

## 🔄 Pull Request Process

1. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes**
   - Write clean, readable code
   - Follow coding standards
   - Add tests for new features
   - Update documentation

3. **Commit your changes**
   ```bash
   git add .
   git commit -m "feat: add some feature"
   ```

   Use conventional commits:
   - `feat:` New feature
   - `fix:` Bug fix
   - `docs:` Documentation
   - `style:` Formatting
   - `refactor:` Code refactoring
   - `test:` Tests
   - `chore:` Maintenance

4. **Sync with upstream**
   ```bash
   git fetch upstream
   git rebase upstream/main
   ```

5. **Push to your fork**
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Create Pull Request**
   - Go to GitHub and create a Pull Request
   - Fill in the PR template
   - Link any related issues

7. **Code Review**
   - Address review comments
   - Push additional commits if needed
   - Once approved, a maintainer will merge

## 📏 Coding Standards

### PHP (PSR-12)

- Use PSR-12 coding standard
- Use type hints and return types
- Document classes and methods with PHPDoc
- Keep methods focused and small
- Use meaningful variable names

Example:
```php
<?php

namespace App\Services;

use App\Models\Household;

class HouseholdService
{
    /**
     * Create a new household with head resident
     *
     * @param array $data
     * @return Household
     */
    public function create(array $data): Household
    {
        return Household::create($data);
    }
}
```

### Laravel Best Practices

- Use Eloquent ORM, avoid raw queries
- Use model observers for model events
- Use form requests for validation
- Use resource controllers
- Use route model binding
- Use database transactions for multiple operations

### Filament Resources

- Follow Filament conventions
- Use resource pages for custom logic
- Use widgets for dashboard components
- Keep forms organized with sections

### Naming Conventions

- **Classes**: PascalCase (`HouseholdResource`)
- **Methods**: camelCase (`createHousehold`)
- **Variables**: camelCase (`$headResident`)
- **Constants**: SCREAMING_SNAKE_CASE (`STATUS_ACTIVE`)
- **Database tables**: snake_case plural (`households`)
- **Database columns**: snake_case (`head_name`)

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter HouseholdTest

# Run with coverage
php artisan test --coverage
```

### Writing Tests

- Write tests for new features
- Write tests for bug fixes
- Follow AAA pattern: Arrange, Act, Assert
- Use descriptive test names

Example:
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Household;

class HouseholdTest extends TestCase
{
    /** @test */
    public function it_creates_head_resident_when_household_is_created()
    {
        // Arrange
        $data = [
            'family_card_number' => '1234567890123456',
            'head_name' => 'John Doe',
            // ...
        ];

        // Act
        $household = Household::create($data);

        // Assert
        $this->assertDatabaseHas('residents', [
            'household_id' => $household->id,
            'name' => 'John Doe',
            'relationship' => 'Kepala Keluarga',
        ]);
    }
}
```

## 📝 Documentation

- Update README.md for user-facing changes
- Update DEPLOYMENT.md for deployment changes
- Add PHPDoc comments to classes and methods
- Update API documentation if applicable

## 🐛 Debugging

- Use Laravel Debugbar in development
- Check `storage/logs/laravel.log`
- Use `dd()` and `dump()` for quick debugging
- Use Xdebug for step debugging

## 💡 Tips

- Keep pull requests focused on a single concern
- Write clear commit messages
- Test your changes thoroughly
- Ask questions if something is unclear
- Be patient and respectful in discussions

## 📧 Getting Help

- GitHub Discussions for questions
- GitHub Issues for bugs and features
- Read the documentation first

## 🙏 Thank You!

Your contributions make this project better for everyone. Thank you for taking the time to contribute!

---

**Happy Coding! 🚀**
