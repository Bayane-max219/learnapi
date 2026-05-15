<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260515122427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema: user, course, lesson, enrollment, certificate, quiz, quiz_question tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            avatar_url VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE course (
            id INT AUTO_INCREMENT NOT NULL,
            instructor_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT NOT NULL,
            level VARCHAR(50) NOT NULL,
            category VARCHAR(100) NOT NULL,
            price NUMERIC(10, 2) NOT NULL,
            thumbnail_url VARCHAR(255) DEFAULT NULL,
            published TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_169E6FB98C4FC193 (instructor_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE lesson (
            id INT AUTO_INCREMENT NOT NULL,
            course_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content LONGTEXT DEFAULT NULL,
            video_url VARCHAR(255) DEFAULT NULL,
            duration_minutes INT NOT NULL DEFAULT 0,
            position INT NOT NULL DEFAULT 0,
            is_free TINYINT(1) NOT NULL DEFAULT 0,
            INDEX IDX_F87474F3591CC992 (course_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE enrollment (
            id INT AUTO_INCREMENT NOT NULL,
            student_id INT NOT NULL,
            course_id INT NOT NULL,
            enrolled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            progress_percent INT NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT \'active\',
            completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX unique_enrollment (student_id, course_id),
            INDEX IDX_DBDCD7E1CB944F1A (student_id),
            INDEX IDX_DBDCD7E1591CC992 (course_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE certificate (
            id INT AUTO_INCREMENT NOT NULL,
            student_id INT NOT NULL,
            course_id INT NOT NULL,
            uuid VARCHAR(64) NOT NULL,
            pdf_url VARCHAR(255) DEFAULT NULL,
            issued_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_219CDE4DD17F50A6 (uuid),
            UNIQUE INDEX unique_certificate (student_id, course_id),
            INDEX IDX_219CDE4DCB944F1A (student_id),
            INDEX IDX_219CDE4D591CC992 (course_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE quiz (
            id INT AUTO_INCREMENT NOT NULL,
            course_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            passing_score INT NOT NULL DEFAULT 70,
            time_limit INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            INDEX IDX_A412FA92591CC992 (course_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE quiz_question (
            id INT AUTO_INCREMENT NOT NULL,
            quiz_id INT NOT NULL,
            question_text LONGTEXT NOT NULL,
            options JSON NOT NULL,
            correct_answer VARCHAR(255) NOT NULL,
            points INT NOT NULL DEFAULT 1,
            position INT NOT NULL DEFAULT 0,
            type VARCHAR(20) NOT NULL DEFAULT \'single\',
            explanation LONGTEXT DEFAULT NULL,
            INDEX IDX_37C837B853D45FE (quiz_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB98C4FC193 FOREIGN KEY (instructor_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDE4DCB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDE4D591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_question ADD CONSTRAINT FK_37C837B853D45FE FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quiz_question DROP FOREIGN KEY FK_37C837B853D45FE');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92591CC992');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDE4DCB944F1A');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDE4D591CC992');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1CB944F1A');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1591CC992');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3591CC992');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB98C4FC193');
        $this->addSql('DROP TABLE quiz_question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE certificate');
        $this->addSql('DROP TABLE enrollment');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE `user`');
    }
}
