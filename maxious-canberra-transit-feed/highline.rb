module HighLine
  # prompt = text to display
  # type can be one of :string, :integer, :float, :bool or a proc
  #   if it's a proc then it is called with the entered string. If the input
  #     cannot be converted then it should throw an exception
  #   if type == :bool then y,yes are converted to true. n,no are converted to
  #     false. All other values are rejected.
  #
  # options should be a hash of validation options
  #   :validate => regular expresion or proc
  #     if validate is a regular expression then the input is matched against it
  #     if it's a proc then the proc is called and the input is accepted if it
  #       returns true
  #   :between => range
  #     the input is checked if it lies within the range
  #   :above => value
  #     the input is checked if it is above the value
  #   :below => value
  #     the input is checked if it is less than the value
  #   :default => string
  #     if the user doesn't enter a value then the default value is returned
  #   :base => [b, o, d, x]
  #     when asking for integers this will take a number in binary, octal,
  #     decimal or hexadecimal
  def ask(prompt, type, options=nil)
    begin
      valid = true

      default = option(options, :default)
      if default
        defaultstr = " |#{default}|"
      else
        defaultstr = ""
      end

      base = option(options, :base)

      print prompt, "#{defaultstr} "
      $stdout.flush
      input = gets.chomp

      if default && input == ""
        input = default
      end

      #comvert the input to the correct type
      input = case type
              when :string: input
              when :integer: convert(input, base) rescue valid = false
              when :float: Float(input) rescue valid = false
              when :bool
                valid = input =~ /^(y|n|yes|no)$/
                input[0] == ?y
              when Proc: input = type.call(input) rescue valid = false
              end

      #validate the input
      valid &&= validate(options, :validate) do |test|
        case test
        when Regexp: input =~ test
        when Proc: test.call(input)
        end
      end
      valid &&= validate(options, :within) { |range| range === input}
      valid &&= validate(options, :above) { |value| input > value}
      valid &&= validate(options, :below) { |value| input < value}

      puts "Not a valid value" unless valid
    end until valid

    return input
  end

  #asks a yes/no question
  def ask_if(prompt)
    ask(prompt, :bool)
  end

  private

  #extracts a key from the options hash
  def option(options, key)
    result = nil
    if options && options.key?(key)
      result = options[key]
    end
    result
  end

  #helper function for validation
  def validate(options, key)
    result = true
    if options && options.key?(key)
      result = yield options[key]
    end
    result    
  end

  #converts a string to an integer
  #input = the value to convert
  #base = the numeric base of the value b,o,d,x
  def convert(input, base)
    if base
      if ["b", "o", "d", "x"].include?(base)
        input = "0#{base}#{input}"
        value = Integer(input)
      else
        value = Integer(input)
      end
    else
      value = Integer(input)
    end

    value
  end
end



if __FILE__ == $0
  include HighLine
  #string input using a regexp to validate, returns test as the default value
  p ask("enter a string, (all lower case)", :string, :validate => /^[a-z]*$/, :default => "test")
  #string input using a proc to validate
  p ask("enter a string, (between 3 and 6 characters)", :string, :validate => proc { |input| (3..6) === input.length})

  #integer intput using :within
  p ask("enter an integer, (0-10)", :integer, :within => 0..10)
  #float input using :above
  p ask("enter a float, (> 6)", :float, :above => 6)

  #getting a binary value
  p ask("enter a binary number", :integer, :base => "b")

  #using a proc to convert the a comma seperated list into an array
  p ask("enter a comma seperated list", proc { |x| x.split(/,/)})

  p ask_if("do you want to continue?")
end
