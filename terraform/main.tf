terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }

  backend "s3" {
    bucket = "channelling-terraform-state-thilinagamage"
    key    = "channelling/terraform.tfstate"
    region = "ap-south-1"
  }
}

provider "aws" {
  region = var.aws_region
}

resource "aws_security_group" "channelling_sg" {
  name        = "channelling-sg"
  description = "Security group for channelling system"

  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name    = "channelling-sg"
    Project = "channelling-system"
  }
}

resource "aws_key_pair" "deployer" {
  key_name   = "channelling-deployer-key"
  public_key = file("${path.module}/deployer_key.pub")
}

resource "aws_instance" "channelling_server" {
  ami                    = var.ami_id
  instance_type          = "t2.micro"
  key_name               = aws_key_pair.deployer.key_name
  vpc_security_group_ids = [aws_security_group.channelling_sg.id]

  tags = {
    Name    = "channelling-server"
    Project = "channelling-system"
  }
}